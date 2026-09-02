<?php

declare(strict_types=1);

namespace App\Command\TLDGroupSync;

use App\Entity\Sales\ProductFamily;
use App\Entity\Sales\ProductFamilyTag;
use App\Entity\Sales\ProductType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

#[AsCommand(name: 'tld:group:sync:catalogue')]
class TLDGroupCatalogueSyncCommand extends Command
{
    private readonly EntityManagerInterface $em;
    private readonly Connection $wordpressConnection;
    private readonly UrlGeneratorInterface $router;

    public function __construct(EntityManagerInterface $em, Connection $wordpressConnection, UrlGeneratorInterface $router)
    {
        parent::__construct();
        $this->setDescription('Sync Catalogue to TLD Group Wordpress database');

        $this->em = $em;
        $this->wordpressConnection = $wordpressConnection;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $productTypeRepository = $this->em->getRepository(ProductType::class);
        $productFamilyRepository = $this->em->getRepository(ProductFamily::class);

        $productTypes = $productTypeRepository->findBy(['publicForTLD' => true]);
        $productFamilies = $productFamilyRepository->findBy(['publicForTLD' => true, 'hidden' => false]);

        foreach (['tld_product_type', 'tld_product'] as $table) {
            $q = $this->wordpressConnection->getDatabasePlatform()->getTruncateTableSQL($table);
            try {
                $this->wordpressConnection->executeStatement($q);
            } catch (Exception $dBALException) {
                $output->writeln('Could not execute query. Reason : '.$dBALException->getMessage());

                return -1;
            }
        }

        /** @var ProductType $productType */
        foreach ($productTypes as $productType) {
            $qb = $this->wordpressConnection->createQueryBuilder();

            $qb
                ->insert('tld_product_type')
                ->setValue('id', ':id')
                ->setValue('en', ':en')
                ->setValue('fr', ':fr')
                ->setValue('ru', ':ru')
                ->setValue('es', ':es')
                ->setValue('pt', ':pt')
                ->setValue('zh', ':zh')
                ->setValue('ja', ':ja')
                ->setValue('de', ':de')
                ->setValue('img_url', ':img_url')
                ->setParameters([
                    'id' => $productType->getLegacyId(),
                    'en' => $productType->getEnglishName() ?? '',
                    'fr' => $productType->getFrenchName() ?? '',
                    'ru' => $productType->getRussianName() ?? '',
                    'es' => $productType->getSpanishName() ?? '',
                    'pt' => $productType->getPortugueseName() ?? '',
                    'zh' => $productType->getChineseName() ?? '',
                    'ja' => $productType->getJapaneseName() ?? '',
                    'de' => $productType->getGermanName() ?? '',
                    'img_url' => null !== $productType->getDms() ? $this->router->generate('dms_photo', ['id' => $productType->getDms()->getLegacyId(), 'd' => (new \DateTime())->format('Y-m-d')], Router::ABSOLUTE_URL) : '',
                ])
            ;

            $this->wordpressConnection->executeQuery($qb->getSQL(), $qb->getParameters());
        }

        /** @var ProductFamily $productFamily */
        foreach ($productFamilies as $productFamily) {
            $qb = $this->wordpressConnection->createQueryBuilder();

            $tags = array_reduce($productFamily->getTags()->toArray(), static function ($memo, ProductFamilyTag $tag) {
                $memo[] = $tag->getName();

                return $memo;
            }, []);

            $qb
                ->insert('tld_product')
                ->setValue('id', ':id')
                ->setValue('type_id', ':type_id')
                ->setValue('model', ':model')
                ->setValue('en', ':en')
                ->setValue('fr', ':fr')
                ->setValue('ru', ':ru')
                ->setValue('es', ':es')
                ->setValue('pt', ':pt')
                ->setValue('zh', ':zh')
                ->setValue('ja', ':ja')
                ->setValue('de', ':de')
                ->setValue('img_url', ':img_url')
                ->setValue('drawing_url', ':drawing_url')
                ->setValue('tags', ':tags')
                ->setParameters([
                    'id' => $productFamily->getLegacyId(),
                    'type_id' => $productFamily->getProductType()->getLegacyId(),
                    'model' => $productFamily->getName(),
                    'en' => $productFamily->getEnglishDescription() ?? '',
                    'fr' => $productFamily->getFrenchDescription() ?? '',
                    'ru' => $productFamily->getRussianDescription() ?? '',
                    'es' => $productFamily->getSpanishDescription() ?? '',
                    'pt' => $productFamily->getPortugueseDescription() ?? '',
                    'zh' => $productFamily->getChineseDescription() ?? '',
                    'ja' => $productFamily->getJapaneseDescription() ?? '',
                    'de' => $productFamily->getGermanDescription() ?? '',
                    'img_url' => null !== $productFamily->getDMSPhoto() ? $this->router->generate('dms_photo', ['id' => $productFamily->getDMSPhoto(), 'd' => (new \DateTime())->format('Y-m-d')], Router::ABSOLUTE_URL) : '',
                    'drawing_url' => null !== $productFamily->getDMSLineDrawing() ? $this->router->generate('dms_photo', ['id' => $productFamily->getDMSLineDrawing(), 'd' => (new \DateTime())->format('Y-m-d')], Router::ABSOLUTE_URL) : '',
                    'tags' => implode(', ', $tags),
                ])
            ;

            $this->wordpressConnection->executeQuery($qb->getSQL(), $qb->getParameters());
        }

        return 0;
    }
}
