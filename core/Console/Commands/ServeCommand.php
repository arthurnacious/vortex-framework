<?php

namespace V8\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use V8\Environment;

class ServeCommand extends Command
{
    protected static $defaultName = 'serve';

    protected function configure(): void
    {
        $this->setDescription('Start the development server')
            ->setHelp('Starts a PHP development server');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $appName = Environment::get('app.name', 'My Vortex-8 App');
        $host =  Environment::get('server.host', 'http://localhost:8000');
        $port = Environment::get('server.port', 8000);
        $publicDir = Environment::get('public.dir', 'public');

        $output->writeln("<info>{$appName} development server started:</info>");
        $output->writeln("<comment>http://{$host}:{$port}</comment>");
        $output->writeln("Press Ctrl+C to stop\n");

        $process = new Process([
            'php',
            '-S',
            "{$host}:{$port}",
            '-t',
            $publicDir
        ]);

        $process->setTimeout(null);
        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        return Command::SUCCESS;
    }
}
