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
            $output->writeln("<error>File not found: $filePath</error>");
            return Command::FAILURE;
        }

        $this->csvReader->setFilePath($filePath);
        $records = $this->csvReader->getRecords();

        $errors = [];
        $recordIndex = 0;
        $validRecords = [];

        foreach ($records as $record) {
            print_r($record);
            $recordIndex++;
            $record = $this->normalizer->normalize($record);
            print_r($record);

            $validationResult = $this->validator->validate($record, $recordIndex);
            if (!$validationResult->isValid()) {
                $errors = array_merge($errors, $validationResult->getErrors());
                continue;
            }

            $validRecords[] = $record;
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $output->writeln("<error>$error</error>");
            }
            return Command::FAILURE;
        }


        $output->writeln('<info>All records are valid!</info>');
        return Command::SUCCESS;
    }
}
