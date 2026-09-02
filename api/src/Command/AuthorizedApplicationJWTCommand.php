<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\AuthorizedApplication;
use App\Security\JWT\JWTEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:authorized_applications:jwt')]
class AuthorizedApplicationJWTCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;
    private readonly JWTEncoder $jwtEncoder;

    public function __construct(JWTEncoder $jwtEncoder, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this
            ->setDescription('Generate a JWT for an existing app')
            ->addArgument('id', InputArgument::REQUIRED, 'ID of the Authorized Application')
        ;
        $this->jwtEncoder = $jwtEncoder;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $authorizedApplication = $this->entityManager->getRepository(AuthorizedApplication::class)->find($id = $input->getArgument('id'));

        if (!$authorizedApplication instanceof AuthorizedApplication) {
            $output->writeln(\sprintf('Could not find the Authorized Application %d', $id));

            return Command::INVALID;
        }

        $output->writeln(\sprintf('JWT for app "%s": %s', $authorizedApplication->name, $this->jwtEncoder->encode([JWTEncoder::PAYLOAD_USER_KEY => $authorizedApplication])));

        return Command::SUCCESS;
    }
}
