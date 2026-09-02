<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006131725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update supplier rankings with new suppliers ressource. And make data migration.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplier_rankings ADD supplier_id INT DEFAULT NULL, ADD disabled_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE supplier_rankings ADD CONSTRAINT FK_10D4CD672ADD6D8C FOREIGN KEY (supplier_id) REFERENCES suppliers (id)');
        $this->addSql('CREATE INDEX IDX_10D4CD672ADD6D8C ON supplier_rankings (supplier_id)');
        $this->addSql('DROP INDEX supplier_number_location ON supplier_rankings');
        $this->addSql('ALTER TABLE supplier_rankings CHANGE location_id location_id INT DEFAULT NULL,  CHANGE supplier_number supplier_number VARCHAR(255) DEFAULT NULL');

        // Update rankings with new suppliers ressource.
        $this->addSql(<<<'SQL'
            UPDATE supplier_rankings
            INNER JOIN suppliers ON suppliers.code = supplier_rankings.supplier_number
            LEFT JOIN suppliers_location ON suppliers_location.supplier_id = suppliers.id AND suppliers_location.location_id = suppliers.location_id
            SET supplier_rankings.supplier_id=suppliers.id
            SQL);

        // Add missing suppliers to rankings.
        $this->addSql(<<<'SQL'
            INSERT INTO supplier_rankings (supplier_id, legacy_id)
            SELECT suppliers.id, 0
            FROM suppliers
            LEFT JOIN supplier_rankings ON (supplier_rankings.supplier_number = suppliers.code OR suppliers.id = supplier_rankings.supplier_id)
            WHERE supplier_rankings.id IS NULL;
            SQL);

        // Disable supplier ranking if the supplier has no supplier, master BU, or don't have Active status.
        $this->addSql(<<<'SQL'
            UPDATE supplier_rankings
                LEFT JOIN suppliers ON supplier_rankings.supplier_id = suppliers.id
            SET supplier_rankings.disabled_at = CURRENT_DATE()
            WHERE
                suppliers.location_id IS NULL
               OR supplier_rankings.supplier_id IS NULL
               OR suppliers.status = 'INACTIVE'
               OR suppliers.status = 'DELETED'
            SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplier_rankings CHANGE location_id location_id INT NOT NULL, CHANGE supplier_number supplier_number VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX supplier_number_location ON supplier_rankings (supplier_number, location_id)');
        $this->addSql('ALTER TABLE supplier_rankings DROP FOREIGN KEY FK_10D4CD672ADD6D8C');
        $this->addSql('DROP INDEX IDX_10D4CD672ADD6D8C ON supplier_rankings');
        $this->addSql('ALTER TABLE supplier_rankings DROP supplier_id, DROP disabled_at');
    }
}
