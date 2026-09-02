<?php

declare(strict_types=1);

namespace App\Command\Parts;

use App\Entity\Parts\SparePartsRequestDeliveryAddress;
use App\Repository\Parts\SparePartsDeliveryAddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:parts:remove_addresses')]
class RemoveUnusedSparePartsDeliveryAddressesCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Remove spare parts delivery addresses unused since more than a month');

        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var SparePartsDeliveryAddressRepository $deliveryAddressRepository */
        $deliveryAddressRepository = $this->entityManager->getRepository(SparePartsRequestDeliveryAddress::class);

        foreach ($deliveryAddressRepository->getUnusedDeliveryAddresses() as $deliveryAddress) {
            $this->entityManager->remove($deliveryAddress);
        }

        $this->entityManager->flush();

        return 0;
    }
}
