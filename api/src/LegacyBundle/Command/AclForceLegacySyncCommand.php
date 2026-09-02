<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Acl;
use App\Repository\AclRepository;
use App\Repository\Directory\LocationRepository;
use LegacyBundle\Event\UpdateEvent;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(name: 'legacy:acl:force_legacy_sync', description: 'Force ACL double write, to sync data between legacy and API base on a specific location (by ERP number). For now only location is concern on update')]
class AclForceLegacySyncCommand
{
    public function __construct(
        private readonly LocationRepository $locationRepository,
        private readonly AclRepository $aclRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument(description: 'ERP number of the location to sync')]
        int $locationErp
    ): int {
        $location = $this->locationRepository->findOneBy(['erp' => $locationErp]);

        if (null === $location) {
            $io->error(\sprintf('No location found for ERP number %d', $locationErp));

            return Command::FAILURE;
        }

        $acls = $this->aclRepository->findBy(['location' => $location]);

        /** @var Acl $acl */
        foreach ($acls as $acl) {
            $event = new UpdateEvent($acl, [
                'location' => [null, $acl->getLocation()],
            ]);

            $io->writeln(\sprintf('Syncing ACL %d with location %d', $acl->getLegacyId(), $acl->getLocation()->getErp()));
            $this->eventDispatcher->dispatch($event);
            $io->writeln(\sprintf('ACL legacy %d synced with location %d', $acl->getLegacyId(), $acl->getLocation()->getErp()));
        }
        $io->success('ACLs synced');

        return Command::SUCCESS;
    }
}
