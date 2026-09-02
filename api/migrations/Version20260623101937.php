<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260623101937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix technician_on_call.deletedAt column name to deleted_at';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call CHANGE deletedAt deleted_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call CHANGE deleted_at deletedAt DATETIME DEFAULT NULL');
    }
}
