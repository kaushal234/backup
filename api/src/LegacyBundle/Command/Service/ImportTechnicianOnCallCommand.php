<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use App\Doctrine\EventListener\EntityChangeListener;
use App\Doctrine\Utils\ListenerManager;
use App\Entity\Common\Airport;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\IndiceFactor;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\ServiceActivity;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCallTag;
use App\Entity\Service\TechnicianOnCallType;
use App\Entity\Support\UnitOperationalStatus;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelper;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:technician_on_call', description: 'Import technicians on call from legacy')]
class ImportTechnicianOnCallCommand extends Command
{
    use TechnicianOnCallListOptionTrait;

    public function __construct(
        private readonly ImportHelper $importHelper,
        private readonly Connection $legacyConnection,
        private readonly EntityCacheHelperFactory $cacheFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly ListenerManager $listenerManager,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $equipmentRecordCache = $this->cacheFactory->createEntityCache(EquipmentRecord::class, 'legacyId');
        $unitOperationalStatusCache = $this->cacheFactory->createEntityCache(UnitOperationalStatus::class, 'name');
        $technicianOnCallTypeCache = $this->cacheFactory->createEntityCache(TechnicianOnCallType::class, 'name');
        $serviceActivityCache = $this->cacheFactory->createEntityCache(ServiceActivity::class, 'name');
        $airportCache = $this->cacheFactory->createEntityCache(Airport::class, 'code');
        $locationCache = $this->cacheFactory->createEntityCache(Location::class, 'legacyId');
        $contactCache = $this->cacheFactory->createEntityCache(ExtranetUser::class, 'legacyId');
        $tagCache = $this->cacheFactory->createEntityCache(TechnicianOnCallTag::class, 'name');
        $customerCache = $this->cacheFactory->createEntityCache(Customer::class, 'legacyId');

        $sql = <<<SQL
                SELECT
                    toc.*,
                    (SELECT GROUP_CONCAT(contact_id) from toc_contacts where parent_id = toc.id) as contacts
                FROM toc
                WHERE id in ({$this->tocIdList})
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->listenerManager->removeListener($this->entityManager, [EntityChangeListener::class]);

        $this->importHelper->disableValidation();
        $this->importHelper->setKeepId(true);

        $this->importHelper->progressiveImport(
            $output,
            $stmt,
            TechnicianOnCall::class,
            'legacyId',
            'id',
            static function (TechnicianOnCall $technicianOnCall, array $data) use (
                $output,
                $peopleCache,
                $equipmentRecordCache,
                $unitOperationalStatusCache,
                $technicianOnCallTypeCache,
                $serviceActivityCache,
                $airportCache,
                $locationCache,
                $contactCache,
                $tagCache,
                $customerCache,
            ) {
                self::skip($data, $output);

                $technicianOnCall->title = html_entity_decode(!empty($data['short_desc']) ? $data['short_desc'] : $data['prob_dsca']);
                $technicianOnCall->originalTitle = $technicianOnCall->title;
                $technicianOnCall->description = !empty($data['prob_dsca']) ? $data['prob_dsca'] : $data['short_desc'];
                $technicianOnCall->originalDescription = $technicianOnCall->description;
                $technicianOnCall->status = self::statusMapping($data['status']);
                $technicianOnCall->createdAt = self::dateFormater($data['dt']);
                $technicianOnCall->updatedAt = self::dateFormater($data['dt']);
                $technicianOnCall->solvedAt = !empty($data['dt_closed']) && '0000-00-00 00:00:00' !== $data['dt_closed'] ? self::dateFormater($data['dt_closed']) : null;
                $technicianOnCall->deletedAt = null;
                $technicianOnCall->unitOperationalStatus = self::unitOperationalStatusMapping($data['unit_operation_status'], $unitOperationalStatusCache, $output);
                $technicianOnCall->indiceFactor = self::indiceFactorMapping($data['ifactor'], $technicianOnCall);
                $technicianOnCall->createdBy = self::fetchEntity($data['postid'], $peopleCache, $output);
                $technicianOnCall->equipmentRecord = self::fetchEntity($data['erid'], $equipmentRecordCache, $output);
                $technicianOnCall->assignee = self::fetchEntity($data['assid'], $peopleCache, $output);
                $technicianOnCall->technician = self::fetchEntity($data['tecid'], $peopleCache, $output);
                $technicianOnCall->errorCodes = $data['error_codes'];
                $technicianOnCall->technicianOnCallType = self::technicianOnCallTypeMapping($data['toc_type'], $technicianOnCallTypeCache, $output);
                $technicianOnCall->serviceActivity = self::serviceActivityMapping($data['activity_type'], $serviceActivityCache, $output);
                $technicianOnCall->symptoms = null;
                $technicianOnCall->rootCause = null;
                $technicianOnCall->solution = null;
                $technicianOnCall->airport = self::setAirport($data['apc'], $airportCache, $technicianOnCall, $output);
                $technicianOnCall->salesOrganisationService = self::fetchEntity($data['ssoid'], $locationCache, $output, true);
                $technicianOnCall->factoryFlag = (bool) $data['factory_support_flag'];
                $technicianOnCall->setMainContact(self::fetchEntity($data['conid'], $contactCache, $output));
                self::addTags($technicianOnCall, $data, $tagCache, $output);
                $technicianOnCall->setLegacyId($data['id']);
                $technicianOnCall->customer = self::fetchCustomer($data, $technicianOnCall, $customerCache, $output);
                $technicianOnCall->thirdPartyName = ($data['third_party'] && 'Y' === $data['third_party']) ? 'Yes' : null;
                $technicianOnCall->warrantyLegacyId = $data['warranty_id'] ? (int) $data['warranty_id'] : null;
                self::addContacts($technicianOnCall, $data['contacts'], $contactCache, $output);
                $technicianOnCall->confidential = 'Y' !== $data['notification'];
                $output->writeln(\sprintf('<info>TOC #%d Correctly imported</info>', $data['id']));
            }
        );

        $sql = <<<'SQL'
                update technician_on_call
                    join customers on technician_on_call.customer_id = customers.id
                set deletedAt = now()
                where customers.deleted_at is not null
            SQL;
        $this->entityManager->getConnection()->executeStatement($sql);

        $sql = <<<'SQL'
                select count(*)
                from tld.toc
                where id not in (select legacy_id from api.technician_on_call)
            SQL;

        $stmt = $this->entityManager->getConnection()->executeQuery($sql);

        $numberNotImportedToc = $stmt->fetchOne();
        $output->writeln(\sprintf('<error>%d TOC not imported</error>', $numberNotImportedToc));

        return Command::SUCCESS;
    }

    public static function skip(array $data, OutputInterface $output)
    {
        if (empty($data['short_desc']) && empty($data['prob_dsca'])) {
            $output->writeln(\sprintf('<error>#%d</error>', $data['id']));
            throw new \InvalidArgumentException('No description available');
        }
    }

    public static function setAirport($airportCode, EntityCacheHelper $repository, TechnicianOnCall $technicianOnCall, OutputInterface $output)
    {
        try {
            return self::fetchEntity($airportCode, $repository, $output, true);
        } catch (\Exception) {
            if (!isset($technicianOnCall->equipmentRecord) || null === $technicianOnCall->equipmentRecord->getAirport()) {
                throw new \InvalidArgumentException('Airport not exist on the legacy TOC');
            }

            return $technicianOnCall->equipmentRecord->getAirport();
        }
    }

    public static function fetchEntity($value, EntityCacheHelper $repository, OutputInterface $output, bool $expectException = false)
    {
        $entity = $repository->fetch((string) $value);

        if ($expectException && !$entity) {
            $output->writeln(\sprintf('<error>%s with value %s on field %s</error>', $repository->getRepository()->getClassName(), !empty($value) ? $value : '"NULL"', $repository->getDescProperty()));
            throw new \InvalidArgumentException(\sprintf('Data not found : %s', $repository->getRepository()->getClassName()));
        }

        return $entity;
    }

    public static function fetchCustomer(array $data, TechnicianOnCall $technicianOnCall, EntityCacheHelper $repository, OutputInterface $output)
    {
        $customer = self::fetchEntity($data['cuid'], $repository, $output);

        if (!$customer) {
            $customer = $technicianOnCall->equipmentRecord->getEndUser();
        }

        if (!$customer) {
            throw new \InvalidArgumentException('Customer and Equipment Record End User not found');
        }

        return $customer;
    }

    private static function statusMapping(string $legacyStatus)
    {
        $mapping = [
            'CLOSED' => 'CLOSED',
            'IN PROGRESS' => 'IN_PROGRESS',
            'SOLVED' => 'SOLVED',
            'SUSPENDED' => 'SUSPENDED',
        ];

        return $mapping[$legacyStatus];
    }

    private static function dateFormater(string $date)
    {
        return new \DateTime($date);
    }

    private static function indiceFactorMapping($legacyValue, TechnicianOnCall $technicianOnCall): string
    {
        if ('1' === $legacyValue && $technicianOnCall->unitOperationalStatus && UnitOperationalStatus::MCF !== $technicianOnCall->unitOperationalStatus->getName()) {
            $legacyValue = '10';
        }

        $legacyValues = [
            '1' => 'IF 1',
            '10' => 'IF 10',
            '100' => 'IF 100',
            '1000' => 'IF 1000',
        ];

        $indiceFactor = $legacyValues[$legacyValue];

        if (!\in_array($indiceFactor, IndiceFactor::values(), true)) {
            throw new \Exception(\sprintf('IndiceFactor %s does not exist. Available values : %s', $indiceFactor, implode(', ', IndiceFactor::values())));
        }

        return $indiceFactor;
    }

    private static function unitOperationalStatusMapping(?string $legacyStatus, EntityCacheHelper $repository, OutputInterface $output): ?UnitOperationalStatus
    {
        if (!$legacyStatus) {
            return null;
        }

        $statusMapping = [
            'MCF' => 'MCF',
            'MCP' => 'MCP',
            'NMC' => 'NMC',
            'FMC' => 'MCF',
        ];

        return self::fetchEntity($statusMapping[$legacyStatus], $repository, $output);
    }

    private static function technicianOnCallTypeMapping(?string $legacyType, EntityCacheHelper $repository, OutputInterface $output): TechnicianOnCallType
    {
        $typeMapping = [
            'Not Defined Yet' => 'toc.type.not_define_yet',
            'Not Defined Yet ' => 'toc.type.not_define_yet',
            'Warranty' => 'toc.type.factory',
            'Payable Services' => 'toc.type.customer',
            'SSO' => 'toc.type.sso',
            'Factory' => 'toc.type.factory',
            'SSO Sales Concession' => 'toc.type.sso',
            null => 'toc.type.not_define_yet',
        ];

        return self::fetchEntity($typeMapping[$legacyType], $repository, $output, false);
    }

    private static function serviceActivityMapping(?string $legacyServiceActivity, EntityCacheHelper $repository, OutputInterface $output): ServiceActivity
    {
        $serviceActivityMapping = [
            'Troubleshooting' => 'Troubleshooting',
            'Commissioning' => 'Commissioning',
            'Service Bulletin' => 'Service Bulletin',
            'Training' => 'Training',
            'Maintenance' => 'Maintenance',
            'Unit Upgrade' => 'Unit Upgrade',
            '' => 'Info request',
        ];

        return self::fetchEntity($serviceActivityMapping[$legacyServiceActivity], $repository, $output, false);
    }

    private static function addTags(TechnicianOnCall $technicianOnCall, array $data, EntityCacheHelper $repository, OutputInterface $output): void
    {
        if ($data['is_ibs']) {
            $ibsTag = self::fetchEntity('toc.tags.ibs', $repository, $output);
            $technicianOnCall->addTag($ibsTag);
        }

        if ($data['is_ihs']) {
            $ihsTag = self::fetchEntity('toc.tags.ihs', $repository, $output);
            $technicianOnCall->addTag($ihsTag);
        }

        if ($data['is_link']) {
            $linkTag = self::fetchEntity('toc.tags.link', $repository, $output);
            $technicianOnCall->addTag($linkTag);
        }
    }

    private static function addContacts(TechnicianOnCall $technicianOnCall, ?string $contactIds, EntityCacheHelper $repository, OutputInterface $output): void
    {
        if (!$contactIds) {
            return;
        }

        foreach (explode(',', $contactIds) as $contactId) {
            $contact = self::fetchEntity($contactId, $repository, $output);

            if (!$contact) {
                continue;
            }
            $technicianOnCall->addContact($contact);
        }
    }
}
