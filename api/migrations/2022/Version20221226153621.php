<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221226153621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add text fields in VWC';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vendor_warranty_claims ADD supplier_stock_verified VARCHAR(255) DEFAULT NULL, ADD tld_stock_verified VARCHAR(255) DEFAULT NULL, ADD issue_origin VARCHAR(255) DEFAULT NULL, ADD corrective_action VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vendor_warranty_claims DROP supplier_stock_verified, DROP tld_stock_verified, DROP issue_origin, DROP corrective_action');
    }
}
