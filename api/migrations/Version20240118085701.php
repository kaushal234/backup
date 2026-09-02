<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240118085701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add currency into SupplierRanking table. And remove is_valid column, previously used for data migrations.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplier_rankings ADD currency_id INT DEFAULT NULL, DROP is_valid');
        $this->addSql('ALTER TABLE supplier_rankings ADD CONSTRAINT FK_10D4CD6738248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('CREATE INDEX IDX_10D4CD6738248176 ON supplier_rankings (currency_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplier_rankings DROP FOREIGN KEY FK_10D4CD6738248176');
        $this->addSql('DROP INDEX IDX_10D4CD6738248176 ON supplier_rankings');
        $this->addSql('ALTER TABLE supplier_rankings ADD is_valid TINYINT(1) NOT NULL, DROP currency_id');
    }
}
