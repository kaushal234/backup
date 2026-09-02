<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250630143745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC - Add columns for Title and Description translation from Deepl done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call ADD english_translated_description LONGTEXT DEFAULT NULL, ADD english_translated_title VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call DROP english_translated_description, DROP english_translated_title');
    }
}
