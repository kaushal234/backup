<?php

declare(strict_types=1);

namespace App\Command\Directory;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:people:remove_acl')]
class RemoveNonStandardAclByPositionCommand extends Command
{
    public function __construct(
        private readonly PeopleRepository $peopleRepository,
        private readonly IriConverterInterface $iriConverter,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
        $this
            ->setDescription('Clean up ACL by removing all non standard permissions by position')
            ->addArgument('position', InputArgument::REQUIRED, 'Position to clean up ACL from')
            ->addArgument('removePiPilot', InputArgument::OPTIONAL, 'Option to know if PI_PILOT groups should be removed', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $positionArgument = $input->getArgument('position');
        $removepiPilotArgument = (bool) $input->getArgument('removePiPilot');
        if (null === $positionArgument) {
            $output->writeln('Position is a mandatory argument.');

            return Command::FAILURE;
        }

        try {
            /** @var Position $position */
            $position = $this->iriConverter->getResourceFromIri($positionArgument);
        } catch (ItemNotFoundException $exception) {
            $output->writeln('Please put a valid Position.');

            return Command::FAILURE;
        }

        $i = 0;
        /** @var People $people */
        foreach ($this->peopleRepository->findBy(['position' => $position, 'hidden' => false, 'disabled' => false]) as $people) {
            foreach ($people->getAcls() as $acl) {
                if ('ACL_AUTH_INTRANET' === $acl->getGroup()->getName()
                    || null !== $acl->getExpiredAt()
                    || 'ACL_AUTH_JAVELO' === $acl->getGroup()->getName()
                    || 'ACL_AUTH_AGILE' === $acl->getGroup()->getName()
                ) {
                    continue;
                }

                if (!$removepiPilotArgument && str_starts_with($acl->getGroup()->getName(), 'pi_PILOT')) {
                    continue;
                }

                $aclToRemove = true;
                foreach ($people->getGroupsForDivision() as $group) {
                    if ($group->getName() === $acl->getGroup()->getName()) {
                        $aclToRemove = false;
                    }
                }

                if ($aclToRemove) {
                    $this->entityManager->remove($acl);
                    ++$i;
                }

                if ($i > 10) {
                    $this->entityManager->flush();
                    $i = 0;
                }
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
