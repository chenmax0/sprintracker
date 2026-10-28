<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Symfony\Command;

use App\Auth\Application\PromoteUserToAdmin\PromoteUserToAdminHandler;
use App\Auth\Application\PromoteUserToAdmin\PromoteUserToAdminPayload;
use App\Auth\Domain\Exception\InvalidEmailException;
use App\Auth\Domain\Exception\UserNotFoundException;
use Assert\LazyAssertionException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:user:promote-admin', description: 'Grants ROLE_ADMIN (access to the admin dashboard) to an existing user')]
final class PromoteUserToAdminCommand extends Command
{
    public function __construct(private PromoteUserToAdminHandler $handler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email of the user to promote');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = (string) $input->getArgument('email');

        try {
            $this->handler->handle(new PromoteUserToAdminPayload($email));
        } catch (LazyAssertionException $e) {
            $io->error(array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions()));

            return Command::FAILURE;
        } catch (InvalidEmailException|UserNotFoundException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('%s is now an administrator.', $email));

        return Command::SUCCESS;
    }
}
