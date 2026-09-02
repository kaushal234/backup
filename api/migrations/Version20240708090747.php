<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240708090747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add contact to CSR (extranet user)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_service_record ADD contact_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_service_record ADD CONSTRAINT FK_3C9EAE6DE7A1254A FOREIGN KEY (contact_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_3C9EAE6DE7A1254A ON customer_service_record (contact_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_service_record DROP FOREIGN KEY FK_3C9EAE6DE7A1254A');
        $this->addSql('DROP INDEX IDX_3C9EAE6DE7A1254A ON customer_service_record');
        $this->addSql('ALTER TABLE customer_service_record DROP contact_id');
    }
}
