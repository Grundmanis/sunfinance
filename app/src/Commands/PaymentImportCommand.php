<?php

namespace App\Commands;

use App\Contracts\Loggers\LoggerInterface;
use App\Contracts\Services\CsvReaderInterface;
use App\Logger\PaymentImportLogger;
use App\Normalization\Csv\PaymentNormalizer;
use App\Validation\PaymentValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
    public const UNKNOWN_ERROR = 99;

    private PaymentValidator $validator;
    private PaymentNormalizer $normalizer;
    private CsvReaderInterface $csvReader;
    private LoggerInterface $logger;

    public function __construct(PaymentValidator $validator, PaymentNormalizer $normalizer, CsvReaderInterface $csvReader, PaymentImportLogger $logger)
    {
        parent::__construct();
        $this->validator = $validator;
        $this->normalizer = $normalizer;
        $this->csvReader = $csvReader;
        $this->logger = $logger;
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
        }

        $output->writeln('<info>All records are valid!</info>');
        return PaymentImportCommand::SUCCESS;
    }

    // TODO: move out
    private function mapErrorToExitCode(array $error): int
    {
        switch ($error['propertyPath']) {
            case 'refId':
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
                return PaymentImportCommand::UNKNOWN_ERROR;
        }
    }
}
