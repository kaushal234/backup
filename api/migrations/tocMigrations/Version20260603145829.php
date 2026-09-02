<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260603145829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC - Add original title and description and remove englishTranslated properties to store it in title and description done';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call ADD original_title VARCHAR(255) NOT NULL, ADD original_description LONGTEXT NOT NULL, DROP english_translated_title, DROP english_translated_description');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call ADD english_translated_title VARCHAR(255) DEFAULT NULL, ADD english_translated_description LONGTEXT DEFAULT NULL, DROP original_title, DROP original_description');
    }
}
