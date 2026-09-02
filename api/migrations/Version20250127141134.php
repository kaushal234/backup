<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250127141134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix migration table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspection CHANGE equipment_record_id equipment_record_id INT NOT NULL');
        $this->addSql('DROP INDEX UNIQ_6B71CBF4AC28B117 ON quote');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6B71CBF4AC28B117 ON quote (quote_number)');
        $this->addSql('ALTER TABLE inspection CHANGE equipment_record_id equipment_record_id INT DEFAULT NULL');
    }
}
