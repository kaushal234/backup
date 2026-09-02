<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Acl;
use App\Repository\AclRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:acl:remove_temporary')]
class RemoveTemporaryAclCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
        $this->setDescription('Remove temporary ACL');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var AclRepository $aclRepository */
        $aclRepository = $this->entityManager->getRepository(Acl::class);
        foreach ($aclRepository->getExpiredAcls() as $acl) {
            $this->entityManager->remove($acl);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
