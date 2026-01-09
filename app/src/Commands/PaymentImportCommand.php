<?php

namespace App\Commands;

use App\Contracts\Services\CsvReaderInterface;
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

    public function __construct(PaymentValidator $validator, PaymentNormalizer $normalizer, CsvReaderInterface $csvReader)
    {
        parent::__construct();
        $this->validator = $validator;
        $this->normalizer = $normalizer;
        $this->csvReader = $csvReader;
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
        $filePath = $input->getArgument('file');

        if (!file_exists($filePath)) {
            // TODO: move to logs
            $output->writeln("<error>File not found: $filePath</error>");
            return PaymentImportCommand::FILE_NOT_EXISTS;
        }

        $this->csvReader->setFilePath($filePath);
        $records = $this->csvReader->getRecords();

        foreach ($records as $record) {
            $record = $this->normalizer->normalize($record);
            $validationResult = $this->validator->validate($record);

            if (!$validationResult->isValid()) {
                foreach ($validationResult->getErrors() as $error) {
                    // log errors here
                    $output->writeln("<error>Field: {$error['propertyPath']}, Value: {$error['invalidValue']}, Message: {$error['message']}</error>");
                    // TODO: refactor
                    switch ($error['propertyPath']) {
                        case 'refId':
                            if ($error['message'] === 'Duplicate entry found for reference.') {
                                return PaymentImportCommand::DUPLICATE;
                            } else {
                                return PaymentImportCommand::MISSING_REF;
                            }
                        case 'amount':
                            return PaymentImportCommand::NEGATIVE_AMOUNT;
                        case 'paymentDate':
                            return PaymentImportCommand::INVALID_DATE;
                        case 'description':
                            return PaymentImportCommand::MISSING_LOAN_NUMBER;
                            break;
                        case 'loanNumber':
                            return PaymentImportCommand::MISSING_LOAN_NUMBER;
                            break;
                    }
                    return PaymentImportCommand::UNKNOWN_ERROR;
                }
            }
        }

        $output->writeln('<info>All records are valid!</info>');
        return PaymentImportCommand::SUCCESS;
    }
}
