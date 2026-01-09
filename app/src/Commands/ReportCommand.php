<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReportCommand extends Command
{
    protected static $defaultName = 'report';

    protected function configure(): void
    {
        $this
            ->setName(ReportCommand::$defaultName)
            ->setDescription('Report for a given date')
            ->addArgument('date', InputArgument::REQUIRED, 'Date for the report');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
    }
}
