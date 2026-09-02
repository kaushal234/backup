<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220906140925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change type of NCR properties to avoid truncated values on migration';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE non_conformity CHANGE problem problem LONGTEXT NOT NULL, CHANGE investigation investigation LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE non_conformity CHANGE problem problem VARCHAR(255) NOT NULL, CHANGE investigation investigation VARCHAR(255) DEFAULT NULL');
    }
}
