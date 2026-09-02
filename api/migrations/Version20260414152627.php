<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260414152627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update and add new emission ratings.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE emission_ratings SET name='TLD LI-ion(BC-3)' WHERE name='TLD Li-ion (not iBS)'");

        // LEGACY DATABASE
        // INSERT INTO tld.lists (id, parent_id, list_name, list_key, list_item) VALUES (4477, 0, 'list.engine.tiers', '', 'TLD LI-ion(BC-5)');
        $this->addSql('INSERT IGNORE INTO emission_ratings VALUES("", "TLD LI-ion(BC-5)", 4477, 0)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE emission_ratings SET name='TLD Li-ion (not iBS)' WHERE name='TLD LI-ion(BC-3)'");
        $this->addSql('DELETE from emission_ratings WHERE name = "TLD LI-ion(BC-5)"');
    }
}
