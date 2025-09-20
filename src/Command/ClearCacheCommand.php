<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:clear-cache',
    description: 'Clear all Symfony caches and warm up',
)]
class ClearCacheCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filesystem = new Filesystem();

        $io->title('Clearing Symfony Cache');

        try {
            // Clear main cache
            $io->section('Clearing main cache...');
            $io->text('Running: php bin/console cache:clear');
            exec('php bin/console cache:clear 2>&1', $output_lines, $return_code);
            if ($return_code !== 0) {
                throw new \Exception('Cache clear failed: ' . implode("\n", $output_lines));
            }
            $io->text('✓ Main cache cleared');

            // Clear Doctrine caches
            $io->section('Clearing Doctrine caches...');
            $commands = [
                'doctrine:cache:clear-metadata',
                'doctrine:cache:clear-query', 
                'doctrine:cache:clear-result'
            ];

            foreach ($commands as $command) {
                $io->text("Running: php bin/console {$command}");
                exec("php bin/console {$command} 2>&1", $output_lines, $return_code);
                if ($return_code !== 0) {
                    $io->warning("Command {$command} failed, but continuing...");
                } else {
                    $io->text("✓ {$command} completed");
                }
            }

            // Warm up cache
            $io->section('Warming up cache...');
            $io->text('Running: php bin/console cache:warmup');
            exec('php bin/console cache:warmup 2>&1', $output_lines, $return_code);
            if ($return_code !== 0) {
                $io->warning('Cache warmup failed, but continuing...');
            } else {
                $io->text('✓ Cache warmed up');
            }

            $io->success('All caches cleared and warmed up successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Failed to clear cache: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
