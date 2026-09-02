<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240425133725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update part number length for FAQ';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE first_article_qualifications_part_numbers CHANGE number number VARCHAR(32) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE first_article_qualifications_part_numbers CHANGE number number VARCHAR(16) NOT NULL');
    }
}
