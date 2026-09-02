<?php

declare(strict_types=1);

namespace App\Command\Legal;

use App\Entity\Directory\People;
use App\Entity\Legal\Contract;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:legal:switch-contract-owner',
    description: 'Reassign contract owners based on business unit rules',
)]
class SwitchContractOwnerCommand extends Command
{
    // Business unit ID => new owner ID
    private const array OWNER_BY_BUSINESS_UNIT = [
        84 => 47903, // Tracteasy => Kersten Roseanna
        114 => 47947, // AES EMEAI => PEDEL Olivier
        55 => 41040, // AES AMERICA => BALTES John
        50 => 21237, // TLD ECAT => PETIT Rodolphe
        107 => 19362, // TLD IMEA => GRZESZEK Jurek
        48 => 35,    // TLD APAC => LESBAUDY Christophe
        49 => 15658,  // 'TLD ECLA' => Pinheiro Guilherme
        16 => 20530, // TLD LAC => MARTINS Rodrigo
        65 => 14967, // SAGE PARTS AMERICAS => CATO Jason
        62 => 21041, // SAGE PARTS APAC => PRATT Christopher
        63 => 2599,  // SAGE PARTS EMEA => COETZEE Stephanie
        19 => 2975,  // AEROSPECIALTIES => JOHNSON Pete
    ];

    // Business units excluded from the final "AMAR/Pinheiro -> Chabrière" reassignment
    private const array EXCLUDED_FROM_FALLBACK_REASSIGNMENT = [26, 69]; // SAS, TAS

    private const array FALLBACK_OWNER_TO_REPLACE = [15658, 22716];
    private const int FALLBACK_NEW_OWNER = 995;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $contractRepository = $this->entityManager->getRepository(Contract::class);
        $contracts = $contractRepository->findAll();

        // Track updated contracts for the final summary
        $updatedContracts = [];

        foreach ($contracts as $contract) {
            $originalOwner = $contract->owner;
            $businessUnitIds = $this->getBusinessUnitIds($contract);

            foreach (self::OWNER_BY_BUSINESS_UNIT as $businessUnitId => $ownerId) {
                if (\in_array($businessUnitId, $businessUnitIds, true)) {
                    $contract->owner = $this->getPeopleReference($ownerId);
                }
            }

            $isExcludedFromFallback = (bool) array_intersect(
                self::EXCLUDED_FROM_FALLBACK_REASSIGNMENT,
                $businessUnitIds
            );

            if (false === $isExcludedFromFallback
                && \in_array($contract->owner->getId(), self::FALLBACK_OWNER_TO_REPLACE, true)
            ) {
                $contract->owner = $this->getPeopleReference(self::FALLBACK_NEW_OWNER);
            }

            if ($contract->owner !== $originalOwner) {
                $updatedContracts[] = [
                    $contract->getId(),
                    $originalOwner->getId(),
                    $contract->owner->getId(),
                ];
            }
        }

        $this->entityManager->flush();

        $io->success(\sprintf('%d contract(s) updated.', \count($updatedContracts)));

        $io->table(
            ['Contract ID', 'Previous owner ID', 'New owner ID'],
            $updatedContracts
        );

        return Command::SUCCESS;
    }

    private function getPeopleReference(int $peopleId): People
    {
        return $this->entityManager->getReference(People::class, $peopleId);
    }

    /**
     * @return int[]
     */
    private function getBusinessUnitIds(Contract $contract): array
    {
        return array_map(
            static fn ($businessUnit) => $businessUnit->getId(),
            $contract->getBusinessUnits()->toArray()
        );
    }
}
