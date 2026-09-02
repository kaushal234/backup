<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240627112250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link hourmeter transaction with API CSR';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE csr_hour_meter_transactions ADD customer_service_record_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE csr_hour_meter_transactions ADD CONSTRAINT FK_A4D726B817DB25B FOREIGN KEY (customer_service_record_id) REFERENCES customer_service_record (id)');
        $this->addSql('CREATE INDEX IDX_A4D726B817DB25B ON csr_hour_meter_transactions (customer_service_record_id)');
        $this->addSql('ALTER TABLE customer_service_record DROP hourmeter');
        $this->addSql('ALTER TABLE intervention DROP hourmeter');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention ADD hourmeter INT DEFAULT NULL');
        $this->addSql('ALTER TABLE csr_hour_meter_transactions DROP FOREIGN KEY FK_A4D726B817DB25B');
        $this->addSql('DROP INDEX IDX_A4D726B817DB25B ON csr_hour_meter_transactions');
        $this->addSql('ALTER TABLE csr_hour_meter_transactions DROP customer_service_record_id');
        $this->addSql('ALTER TABLE customer_service_record ADD hourmeter INT DEFAULT NULL');
    }
}
