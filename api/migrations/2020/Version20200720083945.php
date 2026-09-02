<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200720083945 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE invoice_records (id INT AUTO_INCREMENT NOT NULL, currency_id INT NOT NULL, customer_erp_reference_id INT NOT NULL, original_amount DOUBLE PRECISION NOT NULL, category VARCHAR(255) NOT NULL, invoice_number VARCHAR(255) NOT NULL, expected_payment_date DATETIME NOT NULL, calculated_due_date DATETIME DEFAULT NULL, last_comment LONGTEXT DEFAULT NULL, INDEX IDX_6E446E9538248176 (currency_id), INDEX IDX_6E446E95B8DD6B22 (customer_erp_reference_id), UNIQUE INDEX unique_record_per_invoice (invoice_number, customer_erp_reference_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE account_receivables (id INT AUTO_INCREMENT NOT NULL, customer_erp_reference_id INT DEFAULT NULL, transaction_type_reference_id INT DEFAULT NULL, country_id INT NOT NULL, currency_id INT NOT NULL, order_id INT DEFAULT NULL, invoice_record_id INT DEFAULT NULL, erp_invoice_number VARCHAR(255) NOT NULL, purchase_order_number VARCHAR(255) DEFAULT NULL, invoice_date DATETIME NOT NULL, due_date DATETIME DEFAULT NULL, original_amount DOUBLE PRECISION NOT NULL, original_amount_local_currency DOUBLE PRECISION NOT NULL, balance_amount DOUBLE PRECISION NOT NULL, balance_amount_local_currency DOUBLE PRECISION NOT NULL, sales_order_number INT DEFAULT NULL, sales_reference_a VARCHAR(255) DEFAULT NULL, sales_reference_b VARCHAR(255) DEFAULT NULL, finance_reference_a VARCHAR(255) DEFAULT NULL, finance_reference_b VARCHAR(255) DEFAULT NULL, credit_analyst INT DEFAULT NULL, sales_order_date DATETIME DEFAULT NULL, INDEX IDX_B0C671E8B8DD6B22 (customer_erp_reference_id), INDEX IDX_B0C671E8DE219473 (transaction_type_reference_id), INDEX IDX_B0C671E8F92F3E70 (country_id), INDEX IDX_B0C671E838248176 (currency_id), INDEX IDX_B0C671E88D9F6D38 (order_id), UNIQUE INDEX UNIQ_B0C671E828528490 (invoice_record_id), UNIQUE INDEX unique_record_per_invoice_per_cuno_erp (erp_invoice_number, customer_erp_reference_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX unique_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction_type_references (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, transaction_type_id INT NOT NULL, erp_type VARCHAR(255) NOT NULL, INDEX IDX_E23E316464D218E (location_id), INDEX IDX_E23E3164B3E6B071 (transaction_type_id), UNIQUE INDEX unique_erp_type_per_location (erp_type, location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice_records ADD CONSTRAINT FK_6E446E9538248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('ALTER TABLE invoice_records ADD CONSTRAINT FK_6E446E95B8DD6B22 FOREIGN KEY (customer_erp_reference_id) REFERENCES customer_erp_references (id)');
        $this->addSql('ALTER TABLE account_receivables ADD CONSTRAINT FK_B0C671E8B8DD6B22 FOREIGN KEY (customer_erp_reference_id) REFERENCES customer_erp_references (id)');
        $this->addSql('ALTER TABLE account_receivables ADD CONSTRAINT FK_B0C671E8DE219473 FOREIGN KEY (transaction_type_reference_id) REFERENCES transaction_type_references (id)');
        $this->addSql('ALTER TABLE account_receivables ADD CONSTRAINT FK_B0C671E8F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE account_receivables ADD CONSTRAINT FK_B0C671E838248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('ALTER TABLE account_receivables ADD CONSTRAINT FK_B0C671E88D9F6D38 FOREIGN KEY (order_id) REFERENCES sales_orders (id)');
        $this->addSql('ALTER TABLE account_receivables ADD CONSTRAINT FK_B0C671E828528490 FOREIGN KEY (invoice_record_id) REFERENCES invoice_records (id)');
        $this->addSql('ALTER TABLE transaction_type_references ADD CONSTRAINT FK_E23E316464D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE transaction_type_references ADD CONSTRAINT FK_E23E3164B3E6B071 FOREIGN KEY (transaction_type_id) REFERENCES transaction_types (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account_receivables DROP FOREIGN KEY FK_B0C671E828528490');
        $this->addSql('ALTER TABLE transaction_type_references DROP FOREIGN KEY FK_E23E3164B3E6B071');
        $this->addSql('ALTER TABLE account_receivables DROP FOREIGN KEY FK_B0C671E8DE219473');
        $this->addSql('DROP TABLE invoice_records');
        $this->addSql('DROP TABLE account_receivables');
        $this->addSql('DROP TABLE transaction_types');
        $this->addSql('DROP TABLE transaction_type_references');
    }
}
