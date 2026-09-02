<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorWarrantyClaim;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:purchasing:vwc_followers')]
class ImportVendorWarrantyClaimFollowersCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly IriConverterInterface $iriConverter;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, IriConverterInterface $iriConverter, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Imports VWC followers from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $vendorWarrantyClaimCache = $this->cacheFactory->createEntityCache(VendorWarrantyClaim::class, 'id');

        // Import VWC followers
        $sql = <<<'SQL'
             SELECT id, parent_id, module, list_name, value
             FROM mod_lists
             WHERE module = 'VWC' AND list_name='MEMBERS'
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $iriConverter = $this->iriConverter;

        $this->helper->disableValidation();

        $this->helper->progressiveImport(
            $output, $stmt, Subscription::class, 'id', 'id',
            static function (Subscription $subscription, array $data) use ($peopleCache, $vendorWarrantyClaimCache, $iriConverter) {
                if (null === ($vendorWarrantyClaim = $vendorWarrantyClaimCache->fetch($data['parent_id']))) {
                    throw new \InvalidArgumentException('VWC does not exists');
                }

                if (null === ($user = $peopleCache->fetch($data['value']))) {
                    throw new \InvalidArgumentException('User does not exists');
                }

                $iri = $iriConverter->getIriFromResource($vendorWarrantyClaim);

                $subscription
                    ->setUser($user)
                    ->setResource($iri)
                    ->setCreatedAt(new \DateTime())
                ;
            }, false
        );

        return Command::SUCCESS;
    }
}
