<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201222105833 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE first_article_qualifications ADD product_family_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE first_article_qualifications ADD CONSTRAINT FK_2B0E80D3ADFEE0E7 FOREIGN KEY (product_family_id) REFERENCES product_families (id)');
        $this->addSql('CREATE INDEX IDX_2B0E80D3ADFEE0E7 ON first_article_qualifications (product_family_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE first_article_qualifications DROP FOREIGN KEY FK_2B0E80D3ADFEE0E7');
        $this->addSql('DROP INDEX IDX_2B0E80D3ADFEE0E7 ON first_article_qualifications');
        $this->addSql('ALTER TABLE first_article_qualifications DROP product_family_id');
    }
}
