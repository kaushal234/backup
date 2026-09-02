<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:quality:scar_followers')]
class ImportSupplierCorrectiveActionRequestFollowersCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory)
    {
        parent::__construct();
        $this->setDescription('Imports SCAR followers from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $supplierCorrectiveActionRequestCache = $this->cacheFactory->createEntityCache(SupplierCorrectiveActionRequest::class, 'id');

        // Import SCAR followers
        $sql = <<<'SQL'
             SELECT id, parent_id, module, list_name, value
             FROM mod_lists
             WHERE module = 'SCAR' AND list_name='MEMBERS'
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->progressiveImport(
            $output, $stmt, Subscription::class, 'id', 'id',
            static function (Subscription $subscription, array $data) use ($peopleCache, $supplierCorrectiveActionRequestCache) {
                if (null === $supplierCorrectiveActionRequestCache->fetch($data['parent_id'])) {
                    throw new \InvalidArgumentException('SCAR does not exists');
                }

                if (null === ($user = $peopleCache->fetch($data['value']))) {
                    throw new \InvalidArgumentException('User does not exists');
                }

                $subscription
                    ->setUser($user)
                    ->setResource(\sprintf('/quality/supplier_corrective_action_requests/%s', $data['parent_id']))
                    ->setCreatedAt(new \DateTime())
                ;
            }, false
        );

        return Command::SUCCESS;
    }
}
