<?php

namespace V8\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
        $config = $this->loadServerConfig();

        $output->writeln("<info>Vortex-8 development server started:</info>");
        $output->writeln("<comment>http://{$config['host']}:{$config['port']}</comment>");
        $output->writeln("Press Ctrl+C to stop\n");

        $process = new Process([
            'php',
            '-S',
            "{$config['host']}:{$config['port']}",
            '-t',
            $config['public_dir']
        ]);

        $process->setTimeout(null);
        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        return Command::SUCCESS;
    }

    protected function loadServerConfig(): array
    {
        $config = require __DIR__ . '/../../../config/environment/project.php';

        return [
            'host' => $_ENV['SERVER_HOST'] ?? $config['server']['host'],
            'port' => $_ENV['SERVER_PORT'] ?? $config['server']['port'],
            'public_dir' => $_ENV['PUBLIC_DIR'] ?? $config['server']['public_dir'],
        ];
    }
}
