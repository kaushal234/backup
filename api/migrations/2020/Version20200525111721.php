<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200525111721 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spq_quotations ADD payable_service TINYINT(1) NOT NULL, CHANGE poster_id poster_id INT DEFAULT NULL, CHANGE source_id source_id INT DEFAULT NULL, CHANGE quoter_id quoter_id INT DEFAULT NULL, CHANGE sph_id sph_id INT DEFAULT NULL, CHANGE request_type_id request_type_id INT DEFAULT NULL, CHANGE suspended_at suspended_at DATETIME DEFAULT NULL, CHANGE closed_at closed_at DATETIME DEFAULT NULL, CHANGE rfq rfq VARCHAR(255) DEFAULT NULL, CHANGE baan_sales_order baan_sales_order INT DEFAULT NULL, CHANGE customer_name customer_name VARCHAR(255) DEFAULT NULL, CHANGE incoterms_location incoterms_location VARCHAR(20) DEFAULT NULL, CHANGE submitted_at submitted_at DATETIME DEFAULT NULL, CHANGE forwarding_agent forwarding_agent VARCHAR(3) DEFAULT NULL, CHANGE customer_purchase_order customer_purchase_order VARCHAR(255) DEFAULT NULL, CHANGE routing routing VARCHAR(4) DEFAULT NULL, CHANGE reason reason VARCHAR(25) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_E396917C7B00651C ON spq_quotations (status)');
        $this->addSql('ALTER TABLE spq_quotation_lines CHANGE displayed_part_number displayed_part_number VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE quoted_at quoted_at DATETIME DEFAULT NULL, CHANGE closed_at closed_at DATETIME DEFAULT NULL, CHANGE country_of_origin country_of_origin VARCHAR(3) DEFAULT NULL, CHANGE commodity_code commodity_code VARCHAR(10) DEFAULT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spq_quotation_lines CHANGE displayed_part_number displayed_part_number VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE quoted_at quoted_at DATETIME DEFAULT NULL, CHANGE closed_at closed_at DATETIME DEFAULT NULL, CHANGE country_of_origin country_of_origin VARCHAR(3) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE commodity_code commodity_code VARCHAR(10) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_E396917C7B00651C ON spq_quotations');
        $this->addSql('ALTER TABLE spq_quotations DROP payable_service, CHANGE poster_id poster_id INT DEFAULT NULL, CHANGE source_id source_id INT DEFAULT NULL, CHANGE quoter_id quoter_id INT DEFAULT NULL, CHANGE sph_id sph_id INT DEFAULT NULL, CHANGE request_type_id request_type_id INT DEFAULT NULL, CHANGE suspended_at suspended_at DATETIME DEFAULT NULL, CHANGE closed_at closed_at DATETIME DEFAULT NULL, CHANGE submitted_at submitted_at DATETIME DEFAULT NULL, CHANGE rfq rfq VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE baan_sales_order baan_sales_order INT DEFAULT NULL, CHANGE customer_purchase_order customer_purchase_order VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE customer_name customer_name VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE incoterms_location incoterms_location VARCHAR(20) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE forwarding_agent forwarding_agent VARCHAR(3) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE reason reason VARCHAR(25) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE routing routing VARCHAR(4) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
    }
}
