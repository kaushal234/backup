<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220907080851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change type of SCAR and VWC properties to avoid truncated values on migration';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('ALTER TABLE supplier_corrective_action_request CHANGE issue_origin issue_origin LONGTEXT DEFAULT NULL, CHANGE corrective_action corrective_action LONGTEXT DEFAULT NULL, CHANGE commercial_agreement commercial_agreement LONGTEXT DEFAULT NULL, CHANGE verification_description verification_description LONGTEXT DEFAULT NULL, CHANGE preventive_action preventive_action LONGTEXT DEFAULT NULL, CHANGE conclusion conclusion LONGTEXT DEFAULT NULL, CHANGE supplier_erp supplier_erp INT NOT NULL');
        $this->addSql('ALTER TABLE vendor_warranty_claims CHANGE supplier_shipping_instruction supplier_shipping_instruction LONGTEXT DEFAULT NULL, CHANGE cost_breakdown cost_breakdown LONGTEXT DEFAULT NULL, CHANGE resolution resolution LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
