<?php

declare(strict_types=1);

namespace App\Command\Purchasing\VendorWarrantyClaim;

use App\Entity\Activity\Log;
use App\Entity\Purchasing\VendorWarrantyClaim;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:vwc:date', description: 'Set vendor to respond date on VWC')]
class PopulateVendorWarrantyClaimDateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $this->entityManager->getRepository(VendorWarrantyClaim::class);

        $i = 0;
        /** @var VendorWarrantyClaim $vendorWarrantyClaim */
        foreach ($repository->findAll() as $vendorWarrantyClaim) {
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $queryBuilder
                ->select('l.resource')
                ->addSelect('l.changeSet')
                ->addSelect('l.createdAt')
                ->from(Log::class, 'l')
                ->where($queryBuilder->expr()->eq('l.resource', ':wc_iri'))
                ->orWhere($queryBuilder->expr()->eq('l.resource', ':ncr_iri'))
                ->orderBy('l.createdAt', 'ASC')
                ->setParameter('wc_iri', \sprintf('/purchasing/wc_vendor_warranty_claims/%s', $vendorWarrantyClaim->getId()))
                ->setParameter('ncr_iri', \sprintf('/purchasing/ncr_vendor_warranty_claims/%s', $vendorWarrantyClaim->getId()))
            ;

            $previouslyCreated = null;
            foreach ($queryBuilder->getQuery()->getResult() as $log) {
                if (!isset($log['changeSet']['status'])) {
                    continue;
                }

                if (str_contains($log['changeSet']['status'][1], '/purchasing/vendor_warranty_claims/statuses/3')) {
                    continue;
                }

                $vendorWarrantyClaim->vendorToRespondAt = $log['createdAt'];
                $output->writeln(\sprintf('Date set to VWC #%s', $vendorWarrantyClaim->getId()));
            }

            ++$i;

            if (10000 === $i) {
                $this->entityManager->flush();
                $i = 0;
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
