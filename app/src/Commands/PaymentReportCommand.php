<?php

namespace App\Commands;

use App\Contracts\Loggers\LoggerInterface;
use App\Repository\PaymentRepository;
use App\Utils\DateUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PaymentReportCommand extends Command
{
    protected static $defaultName = 'report';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly PaymentRepository $paymentRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(PaymentReportCommand::$defaultName)
            ->setDescription('Report for a given date')
            ->addArgument('date', InputArgument::REQUIRED, 'Date for the report');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateArg = $input->getArgument('date');
        $this->logger->info("Generating report", ['date' => $dateArg]);

        $date = DateUtil::parseDate($input->getArgument('date'));

        if (!$date) {
            $this->logger->error("Invalid date format", ['date' => $dateArg]);
            $output->writeln('<error>Invalid date format. Use YYYY-MM-DD.</error>');
            return Command::FAILURE;
        }

        $start = $date->setTime(0, 0, 0);
        $end = $date->setTime(23, 59, 59);

        $payments = $this->paymentRepository->fetchBetweenDates($start, $end);

        if (empty($payments)) {
            $this->logger->info("No payments found for date", ['date' => $date]);
            $output->writeln('<info>No payments found for the given date.</info>');
            return Command::SUCCESS;
        }

        $output->writeln("<info>Payments for date: $dateArg</info>");
        foreach ($payments as $payment) {
            $output->writeln(sprintf(
                "ID: %d, Amount: %s, Description: %s, Ref ID: %s",
                $payment->getId(),
                $payment->getAmount(),
                $payment->getDescription(),
                $payment->getRefId()
            ));
        }

        $this->logger->info("Report generated successfully", [
            'date' => $date,
            'paymentCount' => count($payments)
        ]);

        return Command::SUCCESS;
    }
}
