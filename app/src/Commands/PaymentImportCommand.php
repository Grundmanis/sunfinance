<?php

namespace App\Commands;

use App\Contracts\Loggers\LoggerInterface;
use App\Contracts\Services\CsvReaderInterface;
use App\Entity\Payment;
use App\Event\FailedPaymentReportEvent;
use App\Event\LoanPaidEvent;
use App\Event\PaymentReceivedEvent;
use App\Logger\PaymentImportLogger;
use App\Normalization\Csv\PaymentNormalizer;
use App\Validation\PaymentValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PaymentImportCommand extends Command
{
    protected static $defaultName = 'import';

    public const SUCCESS = 0;
    public const DUPLICATE = 1;
    public const NEGATIVE_AMOUNT = 2;
    public const INVALID_DATE = 2;
    public const MISSING_LOAN_NUMBER = 4;
    public const MISSING_REF = 5;
    public const FILE_NOT_EXISTS = 6;
    public const UNKNOWN_LOAN_NUMBER = 7;
    public const UNKNOWN_ERROR = 99;

    private PaymentValidator $validator;
    private PaymentNormalizer $normalizer;
    private CsvReaderInterface $csvReader;
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        PaymentValidator $validator,
        PaymentNormalizer $normalizer,
        CsvReaderInterface $csvReader,
        PaymentImportLogger $logger,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();
        $this->validator = $validator;
        $this->normalizer = $normalizer;
        $this->csvReader = $csvReader;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configure(): void
    {
        $this
            ->setName(PaymentImportCommand::$defaultName)
            ->setDescription('Import a payment CSV')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the CSV file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('Payment import started.');

        $filePath = $input->getArgument('file');

        if (!file_exists($filePath)) {
            $this->logger->warning("File not found: $filePath");
            return PaymentImportCommand::FILE_NOT_EXISTS;
        }

        $this->csvReader->setFilePath($filePath);
        $records = $this->csvReader->getRecords();

        $normalizedRecords = [];

        foreach ($records as $record) {
            $record = $this->normalizer->normalize($record);
            $validationResult = $this->validator->validate($record);

            if (!$validationResult->isValid()) {
                foreach ($validationResult->getErrors() as $error) {
                    $this->logger->warning('Validation error', [
                        'field' => $error['propertyPath'],
                        'value' => $error['invalidValue'],
                        'message' => $error['message'],
                    ]);
                    return $this->mapErrorToExitCode($error);
                }
            }

            $normalizedRecords[] = $record;
        }

        foreach ($normalizedRecords as $record) {
            // TODO: move to validation logic
            $loan = $this->entityManager->getRepository('App\Entity\Loan')
                // TODO: add additional state filter
                ->findOneBy(['reference' => $record['loanNumber']]);
            if (!$loan) {
                $this->logger->warning('Unknown loan number', [
                    'loanNumber' => $record['loanNumber'],
                ]);
                return PaymentImportCommand::UNKNOWN_LOAN_NUMBER;
            }

            $payment = new Payment();
            $payment->setLoanId($loan->getId());
            $payment->setPaymentDate(new \DateTime($record['paymentDate']));
            $payment->setFirstName($record['firstName']);
            $payment->setLastName($record['lastName']);
            $payment->setAmount($record['amount']);
            $payment->setNationalSecurityNumber($record['nationalSecurityNumber'] ?? null);
            $payment->setDescription($record['description']);
            $payment->setRefId($record['refId']);
            $payment->setLoanRef($record['loanNumber']);

            // TODO: test precision issues with decimal calculations
            if ($record['amount'] === $loan->getAmountToPay()) {
                $this->logger->info('Payment matches loan amount to pay', [
                    'loanNumber' => $record['loanNumber'],
                ]);
                // TODO: use constants for states
                $loan->setState('paid');
                $loan->setAmountToPay('0');
                $this->entityManager->persist($loan);
                $payment->setState('assigned');

                // TODO: do not send multiple sms/email to the same customer 
                $this->eventDispatcher->dispatch(
                    new LoanPaidEvent($loan),
                    'loan.fully_paid'
                );
            } else if ($record['amount'] < $loan->getAmountToPay()) {

                $this->logger->info('Payment amount is less than loan amount to pay', [
                    'loanNumber' => $record['loanNumber'],
                ]);

                $newAmountToPay = bcsub($loan->getAmountToPay(), $record['amount'], 2);
                $loan->setAmountToPay($newAmountToPay);
                $this->entityManager->persist($loan);
                $payment->setState('assigned');

                // TODO: do not send multiple sms/email to the same customer 
                $this->eventDispatcher->dispatch(
                    new PaymentReceivedEvent($payment, $loan),
                    'payment.received'
                );
            } else {
                $this->logger->info('Payment amount exceeds loan amount to pay', [
                    'loanNumber' => $record['loanNumber'],
                ]);
                $loan->setState('paid');
                $loan->setAmountToPay('0');

                $payment->setState('partially_assigned');
                $this->entityManager->persist($loan);

                $refundAmount = bcsub($record['amount'], $loan->getAmountToPay(), 2);
                $refundPayment = new Payment();
                $refundPayment->setLoanId($loan->getId());
                $refundPayment->setPaymentDate(new \DateTime($record['paymentDate']));
                $refundPayment->setFirstName($record['firstName']);
                $refundPayment->setLastName($record['lastName']);
                $refundPayment->setAmount($refundAmount);
                $refundPayment->setNationalSecurityNumber($record['nationalSecurityNumber'] ?? null);
                $refundPayment->setDescription('Refund for overpayment of loan ' . $record['loanNumber']);
                $refundPayment->setRefId($record['refId'] . '-REFUND');
                $refundPayment->setLoanRef($record['loanNumber']);
                // TODO: use constants for states
                $refundPayment->setState('refund');

                $this->entityManager->persist($refundPayment);

                // TODO: change event to include refund info
                // TODO: do not send multiple sms/email to the same customer 
                $this->eventDispatcher->dispatch(
                    new PaymentReceivedEvent($payment, $loan, $refundAmount),
                    'payment.received'
                );
            }

            $this->entityManager->persist($payment);
            $this->entityManager->flush();
        }

        $output->writeln('<info>All records are valid!</info>');
        return PaymentImportCommand::SUCCESS;
    }

    // TODO: move out
    private function mapErrorToExitCode(array $error): int
    {
        switch ($error['propertyPath']) {
            case 'refId':
                // TODO: improve duplicate detection
                return $error['message'] === 'Duplicate entry found for reference.'
                    ? PaymentImportCommand::DUPLICATE
                    : PaymentImportCommand::MISSING_REF;
            case 'amount':
                return PaymentImportCommand::NEGATIVE_AMOUNT;
            case 'paymentDate':
                return PaymentImportCommand::INVALID_DATE;
            case 'description':
            case 'loanNumber':
                return PaymentImportCommand::MISSING_LOAN_NUMBER;
            default:
                // TODO; pass payment info
                $this->eventDispatcher->dispatch(
                    new FailedPaymentReportEvent($error),
                    'payments.failed_report'
                );
                return PaymentImportCommand::UNKNOWN_ERROR;
        }
    }
}
