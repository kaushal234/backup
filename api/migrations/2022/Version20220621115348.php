<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220621115348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make supplier erp nullable and add baan supplier';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE non_conformity ADD baan_supplier_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE supplier_corrective_action_request ADD baan_supplier_number VARCHAR(255) DEFAULT NULL, CHANGE supplier_erp supplier_erp INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vendor_warranty_claims ADD baan_supplier_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE non_conformity SET baan_supplier_number = supplier_number');
        $this->addSql('UPDATE supplier_corrective_action_request SET baan_supplier_number = supplier_number');
        $this->addSql('UPDATE vendor_warranty_claims SET baan_supplier_number = supplier_number');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_category_per_business_unit ON directory_position_classification');
        $this->addSql('ALTER TABLE non_conformity DROP baan_supplier_number');
        $this->addSql('ALTER TABLE supplier_corrective_action_request DROP baan_supplier_number, CHANGE supplier_erp supplier_erp INT NOT NULL');
        $this->addSql('ALTER TABLE vendor_warranty_claims DROP baan_supplier_number');
    }
}
