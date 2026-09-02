<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430155548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC Basic part done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE technician_on_call_part (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, technician_on_call_id INT NOT NULL, part_number VARCHAR(255) DEFAULT NULL, vendor_part_number VARCHAR(255) DEFAULT NULL, serial_number VARCHAR(255) DEFAULT NULL, description VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, defective TINYINT(1) NOT NULL, supplier_replaces TINYINT(1) NOT NULL, customer_replaces TINYINT(1) NOT NULL, return_required TINYINT(1) NOT NULL, comment VARCHAR(255) NOT NULL, INDEX IDX_E9E87906B03A8386 (created_by_id), INDEX IDX_E9E87906EC02A7D0 (technician_on_call_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE technician_on_call_part ADD CONSTRAINT FK_E9E87906B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE technician_on_call_part ADD CONSTRAINT FK_E9E87906EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call_part DROP FOREIGN KEY FK_E9E87906B03A8386');
        $this->addSql('ALTER TABLE technician_on_call_part DROP FOREIGN KEY FK_E9E87906EC02A7D0');
        $this->addSql('DROP TABLE technician_on_call_part');
    }
}
