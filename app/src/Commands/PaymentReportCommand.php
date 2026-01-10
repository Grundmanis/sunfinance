<?php

namespace App\Commands;

use App\Contracts\Loggers\LoggerInterface;
use App\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentReportCommand extends Command
{
    protected static $defaultName = 'report';

    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->logger = $logger;
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
        // Try catch ?
        $date = $input->getArgument('date');
        $this->logger->info("Generating report for date", ['date' => $date]);

        if (!\DateTime::createFromFormat('Y-m-d', $date)) {
            $this->logger->error("Invalid date format", ['date' => $date]);
            $output->writeln('<error>Invalid date format. Use YYYY-MM-DD.</error>');
            return Command::FAILURE;
        }

        $startDate = new \DateTime($date . ' 00:00:00');
        $endDate = new \DateTime($date . ' 23:59:59');

        $payments = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Payment::class, 'p')
            ->where('p.paymentDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();

        if (empty($payments)) {
            $this->logger->info("No payments found for date", ['date' => $date]);
            $output->writeln('<info>No payments found for the given date.</info>');
            return Command::SUCCESS;
        }

        $output->writeln("<info>Payments for date: $date</info>");
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
