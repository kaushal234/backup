<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251003133134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update properties from contract';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract ADD description LONGTEXT NOT NULL, CHANGE name short_description VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contract DROP description, CHANGE short_description name VARCHAR(255) NOT NULL');
    }
}
