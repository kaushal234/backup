<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170707122501 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spq_quotation_lines ADD country_of_origin VARCHAR(3) DEFAULT NULL COMMENT \'(DC2Type:string)\', ADD commodity_code VARCHAR(8) DEFAULT NULL COMMENT \'(DC2Type:string)\', ADD item_type VARCHAR(14) NOT NULL COMMENT \'(DC2Type:string)\', CHANGE quantity quantity DOUBLE PRECISION NOT NULL, CHANGE inventory_unit sales_unit VARCHAR(3) NOT NULL COMMENT \'(DC2Type:string)\'');
        $this->addSql('ALTER TABLE spq_quotations ADD forwarding_agent VARCHAR(3) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE incoterms incoterms VARCHAR(3) NOT NULL COMMENT \'(DC2Type:string)\', CHANGE incoterms_location incoterms_location VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spq_quotation_lines DROP country_of_origin, DROP commodity_code, DROP item_type, CHANGE quantity quantity INT NOT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE spq_quotations DROP forwarding_agent, CHANGE incoterms incoterms VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE incoterms_location incoterms_location VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE sales_unit inventory_unit VARCHAR(3) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
    }
}
