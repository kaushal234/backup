<?php

declare(strict_types=1);

namespace App\Command\Sales\ExtranetUser;

use App\Entity\Sales\ExtranetUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:extranet_user:address_verified')]
class ExtranetUserAddressVerifiedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extranetUsers = $this->entityManager->getRepository(ExtranetUser::class)->findBy(['hidden' => false]);
        foreach ($extranetUsers as $extranetUser) {
            $address = $extranetUser->getAddress();
            if ($extranetUser->getUpdatedAt() > new \DateTimeImmutable('-1 year') && !\in_array(null, [
                $address->getStreet1(),
                $address->getPostalCode(),
                $address->getCity(),
                $extranetUser->getExtranetUserProfile()->country?->getName(),
            ], true)) {
                $extranetUser->getExtranetUserProfile()->isVerified = true;
            }

            $this->entityManager->persist($extranetUser);
        }
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
