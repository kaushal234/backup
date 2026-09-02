<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Department;
use App\Entity\Directory\People;
use App\Entity\Directory\Phone;
use App\Entity\Directory\Position;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\PhoneHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

#[AsCommand(name: 'legacy:import:directory:people-relations')]
class ImportDirectoryPeopleRelationsCommand extends Command
{
    // Totally arbitrary constant
    final public const DEFAULT_BUSINESS_UNIT = '7';

    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    private readonly PhoneHelper $phoneHelper;

    private readonly PropertyAccessorInterface $accessor;

    /**
     * ImportDirectoryPeopleRelationsCommand constructor.
     */
    public function __construct(
        ImportHelper $helper,
        Connection $legacyConnection,
        EntityCacheHelperFactory $cacheFactory,
        PhoneHelper $phoneHelper,
        PropertyAccessorInterface $accessor
    ) {
        parent::__construct();
        $this->setDescription('Imports people relations from legacy people table');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->phoneHelper = $phoneHelper;
        $this->accessor = $accessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $businessUnitCache = $this->cacheFactory->createEntityCache(BusinessUnit::class, 'legacyId');
        $departmentCache = $this->cacheFactory->createEntityCache(Department::class, 'legacyId');
        $positionCache = $this->cacheFactory->createEntityCache(Position::class, 'legacyId');
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');

        // Import business units
        $sql = <<<'SQL'
            SELECT * FROM people
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, People::class, 'legacyId', 'id',
            function (People $people, array $data) use ($output, $businessUnitCache, $positionCache, $departmentCache, $peopleCache) {
                if ($data['bu_id']) {
                    $businessUnit = $businessUnitCache->fetch($data['bu_id']);
                    if (null !== $businessUnit) {
                        $people->setBusinessUnit($businessUnit);
                    } else {
                        $defaultBusinessUnit = $businessUnitCache->fetch(static::DEFAULT_BUSINESS_UNIT);
                        $people->setBusinessUnit($defaultBusinessUnit);
                        $output->writeln('');
                        $output->writeln(\sprintf(
                            '<info>Wrong BusinessUnit <comment>%s</comment> for people <comment>%s</comment> => move to <comment>%s</comment></info>',
                            $data['bu_id'], $data['id'], $defaultBusinessUnit->getLegacyId()
                        ));
                    }
                }
                if ($data['fct_id']) {
                    $people->setPosition($positionCache->fetch($data['fct_id']));
                }
                if ($data['dpt_id']) {
                    $people->setDepartment($departmentCache->fetch($data['dpt_id']));
                }
                if ($data['reports_to']) {
                    $people->setSupervisor($peopleCache->fetch($data['reports_to']));
                }

                $phoneFields = [
                    'phone' => Phone::TYPE_RECEPTION,
                    'direct_phone' => Phone::TYPE_PHONE,
                    'home_phone' => Phone::TYPE_HOME,
                    'mobile' => Phone::TYPE_MOBILE,
                    'fax' => Phone::TYPE_FAX,
                ];

                $phones = [];
                $regions = [];
                if ($country = $people->getAddress()->getCountry()) {
                    $regions[] = $country;
                }

                $businessUnit = $people->getBusinessUnit();
                if ($businessUnit && $businessUnit->getLocation()->getAddress()->getCountry()) {
                    $regions[] = $businessUnit->getLocation()->getAddress()->getCountry();
                }
                foreach ($phoneFields as $column => $type) {
                    $phone = $this->phoneHelper->parsePhone($data[$column], array_unique($regions));
                    if (!$phone) {
                        continue;
                    }

                    $phones[] = (new Phone())
                        ->setType($type)
                        ->setNumber($phone)
                    ;
                }
                $this->accessor->setValue($people, 'phones', $phones);
            }, true
        );

        return 0;
    }
}
