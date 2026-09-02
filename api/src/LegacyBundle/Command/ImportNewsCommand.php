<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\News\News;
use App\Entity\News\NewsCategory;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:news')]
class ImportNewsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    private readonly SanitationHelper $sanitationHelper;

    /**
     * ImportNewsCommand constructor.
     */
    public function __construct(
        ImportHelper $helper,
        Connection $legacyConnection,
        EntityCacheHelperFactory $cacheFactory,
        SanitationHelper $sanitationHelper
    ) {
        parent::__construct();
        $this->setDescription('Import TLD legacy news and news categories');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->sanitationHelper = $sanitationHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $categoryCache = $this->cacheFactory->createEntityCache(NewsCategory::class, 'legacyId');

        // Import news categories
        $sql = <<<'SQL'
            SELECT id, list_item
            FROM lists
            WHERE list_name = 'categories'
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, NewsCategory::class, 'legacyId', 'id',
            static function (NewsCategory $category, array $data) {
                $category
                    ->setLegacyId((int) $data['id'])
                    ->setName($data['list_item'])
                ;
            }, true
        );

        // Import news
        $sql = <<<'SQL'
            SELECT id, title, en, date, cat
            FROM internal_news
            WHERE date > '2006-08-01';
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, News::class, 'legacyId', 'id',
            function (News $news, array $data) use ($categoryCache) {
                $news
                    ->setLegacyId((int) $data['id'])
                    ->setTitle(strip_tags($this->sanitationHelper->parse($data['title'])))
                    ->setContent($this->sanitationHelper->parse($data['en'], true, true, true, false))
                    ->setDate(new \DateTime($data['date']))
                    ->setCategory($categoryCache->fetch($data['cat']))
                ;
            }, true
        );

        return 0;
    }
}
