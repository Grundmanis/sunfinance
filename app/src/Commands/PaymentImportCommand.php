<?php

namespace App\Commands;

use App\Contracts\Loggers\LoggerInterface;
use App\Contracts\Services\CsvReaderInterface;
use App\Event\FailedPaymentReportEvent;
use App\Logger\PaymentImportLogger;
use App\Normalization\Csv\PaymentNormalizer;
use App\Services\PaymentService;
use App\Validation\PaymentValidator;
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
    private EventDispatcherInterface $eventDispatcher;
    private PaymentService $paymentService;

    public function __construct(
        PaymentValidator $validator,
        PaymentNormalizer $normalizer,
        CsvReaderInterface $csvReader,
        PaymentImportLogger $logger,
        EventDispatcherInterface $eventDispatcher,
        PaymentService $paymentService
    ) {
        parent::__construct();
        $this->validator = $validator;
        $this->normalizer = $normalizer;
        $this->csvReader = $csvReader;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->paymentService = $paymentService;
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
        // Try catch ?
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
            // try catch ?
            $result = $this->paymentService->processPayment($record);
            // if ($result !== PaymentImportCommand::SUCCESS) {
            //     $this->logger->warning('Payment processing error', [
            //         'loanNumber' => $record['loanNumber'],
            //         'refId' => $record['refId'],
            //         'amount' => $record['amount'],
            //     ]);
            //     return $result;
            // }
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
