<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221152348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change CUNO field to Infor LN Business Partner Code';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_relationship_teams ADD customer_business_partner_code VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE customers ADD infor_ln_business_partner_codes TINYTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE sales_orders ADD infor_ln_business_partner_code VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customers DROP infor_ln_business_partner_codes');
        $this->addSql('ALTER TABLE sales_orders DROP infor_ln_business_partner_code');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP customer_business_partner_code');
    }
}
