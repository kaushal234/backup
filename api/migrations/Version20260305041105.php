<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305041105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace subCategory string field by an array.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE power_bi_reports ADD sub_categories JSON DEFAULT \'[]\' NOT NULL, DROP sub_category');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE power_bi_reports ADD sub_category VARCHAR(255) DEFAULT NULL, DROP sub_categories');
    }
}
