<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Directory;

use App\Entity\Directory\People;
use App\Entity\Directory\Premise;
use App\Entity\Directory\PremiseTag;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;
use App\Repository\Directory\PeopleRepository;
use Symfony\Bundle\SecurityBundle\Security;

class PremiseSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    private ?People $user = null;

    public function __construct(
        private readonly PeopleRepository $peopleRepository,
        private readonly Security $security,
    ) {
    }

    public function getComputedColumns(): array
    {
        return ['address', 'tags', 'count'];
    }

    /**
     * @param Premise $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        $value = null;
        switch ($column) {
            case 'count':
                $value = $this->peopleRepository->countPremiseUsers($item);
                break;
            case 'tags':
                $value = implode(', ', array_map(static fn (PremiseTag $tag) => $tag->getName(), $item->getTags()->toArray()));
                break;
            case 'address':
                if (null === $this->user) {
                    /** @var People $user */
                    $user = $this->security->getUser();
                    $this->user = $user;
                }
                $street1 = $item->address->getStreet1();
                $street2 = $item->address->getStreet2();
                $postalCode = $item->address->getPostalCode();

                if ($this->user->getPremise() !== $item
                    && !$this->security->isGranted('FEATURE_PREMISE_WRITE')
                    && \in_array(PremiseTag::HOME_OFFICE_RESTRICTED, array_column($item->getTags()->toArray() ?? [], 'name'), true)) {
                    if ($street1 ?? null) {
                        $street1 = '***';
                    }
                    if ($street2 ?? null) {
                        $street2 = '***';
                    }
                    if ($postalCode ?? null) {
                        $postalCode = '***';
                    }
                }

                $value = \sprintf('%s, %s, %s, %s %s,%s, %s', $street1, $street2, $postalCode, $item->address->getCity(), $item->address->getTown(), $item->address->getState(), $item->address->getCountry());
        }

        return $value;
    }

    public function supports(string $class, string $operationName): bool
    {
        return Premise::class === $class;
    }
}
