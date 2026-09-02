<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220214220755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new columns in DMS sync';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dms ADD type VARCHAR(60) DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL, CHANGE portal portal VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dms DROP type, DROP created_at, CHANGE portal portal VARCHAR(10) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
