<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170420075514 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE spq_quotation_lines (id INT AUTO_INCREMENT NOT NULL, quotation_id INT NOT NULL, position INT NOT NULL, status VARCHAR(25) NOT NULL, part_number VARCHAR(25) NOT NULL, displayed_part_number VARCHAR(255) DEFAULT NULL, description VARCHAR(255) NOT NULL, unit_base_price DOUBLE PRECISION NOT NULL, total_price DOUBLE PRECISION NOT NULL, currency VARCHAR(10) NOT NULL, quantity INT NOT NULL, discount DOUBLE PRECISION NOT NULL, commission DOUBLE PRECISION NOT NULL, leadtime SMALLINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, quoted_at DATETIME DEFAULT NULL, closed_at DATETIME DEFAULT NULL, comment LONGTEXT NOT NULL, tax DOUBLE PRECISION NOT NULL, INDEX IDX_8A7CA6B9B4EA4E60 (quotation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spq_quotation_files (id INT NOT NULL, quotation_id INT DEFAULT NULL, INDEX IDX_C3486A76B4EA4E60 (quotation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spq_quotations (id INT AUTO_INCREMENT NOT NULL, poster_id INT DEFAULT NULL, source_id INT DEFAULT NULL, quoter_id INT DEFAULT NULL, sph_id INT DEFAULT NULL, request_type_id INT DEFAULT NULL, created_at DATETIME NOT NULL, received_at DATE NOT NULL, suspended_at DATETIME DEFAULT NULL, expired_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, rfq VARCHAR(255) DEFAULT NULL, baan_customer_number VARCHAR(6) DEFAULT NULL, baan_sales_order INT DEFAULT NULL, status VARCHAR(25) NOT NULL, customer_name VARCHAR(255) DEFAULT NULL, contact_emails TEXT NOT NULL, comment TEXT NOT NULL, shipped_complete TINYINT(1) NOT NULL, payment_terms VARCHAR(255) NOT NULL, delivery_address TEXT NOT NULL, invoicing_address TEXT NOT NULL, language VARCHAR(255) NOT NULL, header_text TEXT NOT NULL, incoterms VARCHAR(255) NOT NULL, incoterms_location VARCHAR(255) NOT NULL, INDEX IDX_E396917C5BB66C05 (poster_id), INDEX IDX_E396917C953C1C61 (source_id), INDEX IDX_E396917C1C8A31F (quoter_id), INDEX IDX_E396917CA234FDCD (sph_id), INDEX IDX_E396917CEF68FEC4 (request_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spq_request_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, UNIQUE INDEX UNIQ_E4CCDAEB5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spq_attached_files (id INT NOT NULL, quotation_id INT DEFAULT NULL, INDEX IDX_5181918DB4EA4E60 (quotation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE spq_quotation_lines ADD CONSTRAINT FK_8A7CA6B9B4EA4E60 FOREIGN KEY (quotation_id) REFERENCES spq_quotations (id)');
        $this->addSql('ALTER TABLE spq_quotation_files ADD CONSTRAINT FK_C3486A76B4EA4E60 FOREIGN KEY (quotation_id) REFERENCES spq_quotations (id)');
        $this->addSql('ALTER TABLE spq_quotation_files ADD CONSTRAINT FK_C3486A76BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE spq_quotations ADD CONSTRAINT FK_E396917C5BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE spq_quotations ADD CONSTRAINT FK_E396917C953C1C61 FOREIGN KEY (source_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE spq_quotations ADD CONSTRAINT FK_E396917C1C8A31F FOREIGN KEY (quoter_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE spq_quotations ADD CONSTRAINT FK_E396917CA234FDCD FOREIGN KEY (sph_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE spq_quotations ADD CONSTRAINT FK_E396917CEF68FEC4 FOREIGN KEY (request_type_id) REFERENCES spq_request_types (id)');
        $this->addSql('ALTER TABLE spq_attached_files ADD CONSTRAINT FK_5181918DB4EA4E60 FOREIGN KEY (quotation_id) REFERENCES spq_quotations (id)');
        $this->addSql('ALTER TABLE spq_attached_files ADD CONSTRAINT FK_5181918DBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('INSERT IGNORE INTO spq_request_types (name) VALUES ("email"), ("phone"), ("fax"), ("other")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_QUOTATION_WRITE"
                          AND user_group.name = "SUPERUSER"');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE spq_quotation_lines DROP FOREIGN KEY FK_8A7CA6B9B4EA4E60');
        $this->addSql('ALTER TABLE spq_quotation_files DROP FOREIGN KEY FK_C3486A76B4EA4E60');
        $this->addSql('ALTER TABLE spq_attached_files DROP FOREIGN KEY FK_5181918DB4EA4E60');
        $this->addSql('ALTER TABLE spq_quotations DROP FOREIGN KEY FK_E396917CEF68FEC4');
        $this->addSql('DROP TABLE spq_quotation_lines');
        $this->addSql('DROP TABLE spq_quotation_files');
        $this->addSql('DROP TABLE spq_quotations');
        $this->addSql('DROP TABLE spq_request_types');
        $this->addSql('DROP TABLE spq_attached_files');
    }
}
