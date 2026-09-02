<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251118082829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add status observation property to contract ';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contract ADD observation_status VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contract DROP observation_status');
    }
}
