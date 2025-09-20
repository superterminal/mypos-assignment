<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clear-data',
    description: 'Clear all vehicle and user data from the database',
)]
class ClearDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Force clear without confirmation');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('force')) {
            if (!$io->confirm('Are you sure you want to clear all data? This action cannot be undone.', false)) {
                $io->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $io->title('Clearing Database Data');

        // Clear vehicles first (due to foreign key constraints)
        $vehicleCount = $this->entityManager->getRepository(Vehicle::class)->count([]);
        $this->entityManager->createQuery('DELETE FROM App\Entity\Vehicle')->execute();
        $io->text("✓ Cleared {$vehicleCount} vehicles");

        // Clear users
        $userCount = $this->entityManager->getRepository(User::class)->count([]);
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $io->text("✓ Cleared {$userCount} users");

        $io->success('Database cleared successfully!');

        return Command::SUCCESS;
    }
}
