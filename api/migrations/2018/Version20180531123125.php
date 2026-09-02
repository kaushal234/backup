<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180531123125 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE product_dms (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', product_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', dms_id INT NOT NULL COMMENT \'(DC2Type:integer)\', dms_type VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', INDEX IDX_49EFDB9D4584665A (product_id), INDEX IDX_49EFDB9DA38F4C43 (dms_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_dms ADD CONSTRAINT FK_49EFDB9D4584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE product_dms ADD CONSTRAINT FK_49EFDB9DA38F4C43 FOREIGN KEY (dms_id) REFERENCES dms (id)');
        $this->addSql('ALTER TABLE products ADD description TEXT DEFAULT NULL, CHANGE erp_location_id erp_location_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('UPDATE product_family_dms, dms SET dms_id = dms.id WHERE dms_id = dms.legacy_id');
        $this->addSql('ALTER TABLE product_family_dms ADD CONSTRAINT FK_FCD76E2AA38F4C43 FOREIGN KEY (dms_id) REFERENCES dms (id)');
        $this->addSql('CREATE INDEX IDX_FCD76E2AA38F4C43 ON product_family_dms (dms_id)');
        $this->addSql('ALTER TABLE product_types ADD CONSTRAINT FK_F86CF26CA38F4C43 FOREIGN KEY (dms_id) REFERENCES dms (id)');
        $this->addSql('CREATE INDEX IDX_F86CF26CA38F4C43 ON product_types (dms_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE product_dms');
        $this->addSql('ALTER TABLE product_family_dms DROP FOREIGN KEY FK_FCD76E2AA38F4C43');
        $this->addSql('DROP INDEX IDX_FCD76E2AA38F4C43 ON product_family_dms');
        $this->addSql('ALTER TABLE product_types DROP FOREIGN KEY FK_F86CF26CA38F4C43');
        $this->addSql('DROP INDEX IDX_F86CF26CA38F4C43 ON product_types');
        $this->addSql('ALTER TABLE products DROP description, CHANGE erp_location_id erp_location_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
    }
}
