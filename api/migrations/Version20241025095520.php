<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241025095520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update after removing baan';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wms_picking_alerts DROP FOREIGN KEY FK_BDD16DF6DE12AB56');
        $this->addSql('DROP TABLE wms_picking_alerts');
        $this->addSql('ALTER TABLE competitor_pricings ADD currency_id INT DEFAULT NULL, ADD incoterm_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE competitor_pricings ADD CONSTRAINT FK_CC696AE838248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('ALTER TABLE competitor_pricings ADD CONSTRAINT FK_CC696AE87055C866 FOREIGN KEY (incoterm_id) REFERENCES incoterm (id)');
        $this->addSql('CREATE INDEX IDX_CC696AE838248176 ON competitor_pricings (currency_id)');
        $this->addSql('CREATE INDEX IDX_CC696AE87055C866 ON competitor_pricings (incoterm_id)');
        $this->addSql('ALTER TABLE forecast_closures ADD currency_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE forecast_closures ADD CONSTRAINT FK_B6C68D9238248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('CREATE INDEX IDX_B6C68D9238248176 ON forecast_closures (currency_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE wms_picking_alerts (id INT AUTO_INCREMENT NOT NULL, created_by INT NOT NULL, erp INT NOT NULL, warehouse VARCHAR(3) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, location VARCHAR(8) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, part_number VARCHAR(25) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, operation INT DEFAULT NULL, work_order INT DEFAULT NULL, created_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, comment VARCHAR(512) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, status VARCHAR(10) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, type VARCHAR(25) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, quantity_picked DOUBLE PRECISION DEFAULT NULL, quantity_requested DOUBLE PRECISION DEFAULT NULL, inventory_unit VARCHAR(3) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, stock DOUBLE PRECISION DEFAULT NULL, INDEX IDX_BDD16DF6FC51BA91 (erp), INDEX IDX_BDD16DF67B00651C (status), INDEX IDX_BDD16DF6DE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE wms_picking_alerts ADD CONSTRAINT FK_BDD16DF6DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE competitor_pricings DROP FOREIGN KEY FK_CC696AE838248176');
        $this->addSql('ALTER TABLE competitor_pricings DROP FOREIGN KEY FK_CC696AE87055C866');
        $this->addSql('DROP INDEX IDX_CC696AE838248176 ON competitor_pricings');
        $this->addSql('DROP INDEX IDX_CC696AE87055C866 ON competitor_pricings');
        $this->addSql('ALTER TABLE competitor_pricings DROP currency_id, DROP incoterm_id');
        $this->addSql('ALTER TABLE forecast_closures DROP FOREIGN KEY FK_B6C68D9238248176');
        $this->addSql('DROP INDEX IDX_B6C68D9238248176 ON forecast_closures');
        $this->addSql('ALTER TABLE forecast_closures DROP currency_id');
    }
}
