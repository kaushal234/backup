<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Directory\JuridicalLocation;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Order;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:orders')]
class ImportSalesOrdersCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly SanitationHelper $sanitationHelper;
    private readonly IriConverterInterface $iriConverter;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, SanitationHelper $sanitationHelper, IriConverterInterface $iriConverter, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Imports SOR from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->sanitationHelper = $sanitationHelper;
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $locationCache = $this->cacheFactory->createEntityCache(Location::class, 'erp');
        $juridicalLocationCache = $this->cacheFactory->createEntityCache(JuridicalLocation::class, 'legacyId');
        $customerCache = $this->cacheFactory->createEntityCache(Customer::class, 'legacyId');
        $customerNameCache = $this->cacheFactory->createEntityCache(Customer::class, 'name');

        $sql = <<<'SQL'
            SELECT id, bu, juridical_entity_id, t_cuno, dt_entered, dt_closed, status, eqno, orno, asm, buyer_customer_id, user_customer_id, cu_nama, cu_new, cu_orno, src_xml  FROM sor
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->progressiveImport(
            $output, $stmt, Order::class, 'legacyId', 'id',
            function (Order $order, array $data) use ($peopleCache, $customerCache, $customerNameCache, $locationCache, $juridicalLocationCache) {
                /** @var Customer|null $buyer */
                $buyer = null !== $data['buyer_customer_id'] ? $customerCache->fetch($data['buyer_customer_id']) : $customerNameCache->fetch($data['cu_nama']) ?? $customerNameCache->fetch(mb_trim((string) $data['cu_nama']));
                $endUser = null !== $data['buyer_customer_id'] ? $customerCache->fetch($data['buyer_customer_id']) : $customerNameCache->fetch($data['cu_nama']) ?? $customerNameCache->fetch(mb_trim((string) $data['cu_nama']));
                if ($data['buyer_customer_id'] !== $data['user_customer_id']) {
                    $endUser = null !== $data['user_customer_id'] ? $customerCache->fetch($data['user_customer_id']) : $customerNameCache->fetch($data['cu_nama']) ?? $customerNameCache->fetch(mb_trim((string) $data['cu_nama']));
                }
                /** @var string $status */
                $status = 'CREATE_SALES_SO' === $data['status'] ? 'PENDING' : str_replace('_', ' ', (string) $data['status']);

                $baanOrderNumbers = [];
                if (null !== $orderNumber = $this->sanitationHelper->trimAndNullify($data['orno'])) {
                    $orderNumber = str_replace(' ', '', $orderNumber);
                    $orderNumber = mb_trim(str_replace([';', '/'], ',', $orderNumber), ',');

                    $baanOrderNumbers = explode(',', $orderNumber);
                    $baanOrderNumbers = array_splice($baanOrderNumbers, 0, 14);
                }

                $order
                    ->setLegacyId((int) $data['id'])
                    ->setSso($locationCache->fetch($data['bu']))
                    ->setJuridicalLocation($juridicalLocationCache->fetch($data['juridical_entity_id']))
                    ->setBaanCustomerNumber($this->sanitationHelper->trimAndNullify($data['t_cuno']))
                    ->setEnteredAt(new \DateTime($data['dt_entered']))
                    ->setClosedAt(null === $data['dt_closed'] || '0000-00-00' === $data['dt_closed'] ? null : new \DateTime($data['dt_closed']))
                    ->setStatus($status)
                    ->setEquoteId($this->sanitationHelper->trimAndNullify($data['eqno']))
                    ->setBaanOrderNumbers($baanOrderNumbers)
                    ->setAsm($peopleCache->fetch($data['asm']))
                    ->setBuyer($buyer)
                    ->setEndUser($endUser)
                    ->setCustomerName(mb_trim((string) $data['cu_nama']))
                    ->setNewCustomer('Y' === $data['cu_new'])
                    ->setCustomerPurchaseOrders((array) $this->sanitationHelper->trimAndNullify($data['cu_orno']))
                    ->setXmlSource($data['src_xml'])
                ;
            }
        );

        $locationCache = null;
        $peopleCache = null;
        $customerCache = null;
        $juridicalLocationCache = null;
        $customerNameCache = null;
        $this->entityManager->clear();

        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');

        $sql = <<<'SQL'
            SELECT COUNT(mod_logs.id) AS cnt
            FROM mod_logs
            INNER JOIN sor ON sor.id = mod_logs.parent_id and module = "SOR"
            INNER JOIN people ON mod_logs.poster = people.id
            SQL;

        $result = $this->legacyConnection->fetchOne($sql);
        $count = (int) $result['cnt'];
        $maxResults = 1_000;

        $events = $this->entityManager->getClassMetadata(Comment::class)->lifecycleCallbacks;
        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks([]);

        for ($offset = 0; $offset < $count; $offset += $maxResults) {
            $sql = \sprintf('    SELECT mod_logs.id, mod_logs.parent_id, mod_logs.date, mod_logs.poster, mod_logs.comment
    FROM mod_logs
    INNER JOIN sor ON sor.id = mod_logs.parent_id and module = "SOR"
    INNER JOIN people ON mod_logs.poster = people.id
    LIMIT %s OFFSET %s', $maxResults, $offset);
            $output->writeln(\sprintf('Importing logs from %s to %s on %s', $offset, $offset + $maxResults - 1, $count));
            $stmt = $this->legacyConnection->executeQuery($sql);
            $resourceCache = $this->cacheFactory->createEntityCache(Order::class, 'legacyId');

            $this->helper->progressiveImport(
                $output, $stmt, Comment::class, 'legacyId', 'id',
                function (Comment $comment, array $data) use ($peopleCache, $resourceCache) {
                    if (!$order = $resourceCache->fetch($data['parent_id'])) {
                        return;
                    }

                    $comment
                        ->setMessage($this->sanitationHelper->parse($data['comment']))
                        ->setCreatedAt($date = new \DateTime($data['date']))
                        ->setUpdatedAt($date)
                        ->setResource($this->iriConverter->getIriFromResource($order))
                        ->setUser($peopleCache->fetch($data['poster']))
                    ;
                }
            );
            $this->entityManager->clear();
        }

        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks($events);

        return 0;
    }
}
