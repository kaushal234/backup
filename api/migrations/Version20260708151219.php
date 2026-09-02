<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260708151219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC defective parts';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE technician_on_call_defective_part (part_number VARCHAR(255) DEFAULT NULL, description VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, technician_on_call_id INT NOT NULL, INDEX IDX_F6EABEC4B03A8386 (created_by_id), INDEX IDX_F6EABEC4EC02A7D0 (technician_on_call_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE technician_on_call_defective_part ADD CONSTRAINT FK_F6EABEC4B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE technician_on_call_defective_part ADD CONSTRAINT FK_F6EABEC4EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call_defective_part DROP FOREIGN KEY FK_F6EABEC4B03A8386');
        $this->addSql('ALTER TABLE technician_on_call_defective_part DROP FOREIGN KEY FK_F6EABEC4EC02A7D0');
        $this->addSql('DROP TABLE technician_on_call_defective_part');
    }
}
