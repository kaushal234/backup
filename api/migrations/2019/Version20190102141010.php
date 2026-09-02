<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Version20190102141010 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function up(Schema $schema): void
    {
        $this->addSql('TRUNCATE TABLE first_article_qualifications_members');
        $this->addSql('TRUNCATE TABLE first_article_qualifications_part_numbers');
        $this->addSql('TRUNCATE TABLE first_article_qualifications_plan_items');
        $this->addSql('TRUNCATE TABLE first_article_qualifications_tags_xref');
        $this->addSql('TRUNCATE TABLE first_article_qualifications_equipment_records');
        $this->addSql("DELETE FROM activity where resource LIKE '/quality/first_article_qualification%'");
        $this->addSql('TRUNCATE TABLE first_article_qualifications_files');
        $this->addSql("DELETE FROM files WHERE discr ='faq_file'");
        $this->addSql('DELETE FROM first_article_qualifications');
        $this->addSql('ALTER TABLE first_article_qualifications AUTO_INCREMENT = 1');
    }

    public function postUp(Schema $schema): void
    {
        parent::postUp($schema);
        $finder = new Finder();
        $fs = new Filesystem();
        $fs->remove($finder->files()->in($this->container->getParameter('legacy.upload_dir').'/quality/first_article_qualification'));
    }

    public function down(Schema $schema): void
    {
        // TODO: Implement down() method.
    }

    /**
     * Sets the container.
     */
    public function setContainer(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
