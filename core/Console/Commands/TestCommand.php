<?php

namespace V8\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class TestCommand extends Command
{
    protected static $defaultName = 'test';

    protected function configure()
    {
        $this->setDescription('Run the test suite')
            ->setHelp('Executes PHPUnit tests');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $process = new Process(['./vendor/bin/phpunit'], timeout: null);
        $process->run(fn($type, $buffer) => $output->write($buffer));

        return $process->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
    }
}
