<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240529140209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update types indice factor and order to display';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO type (description, type, indice_factor, displayed_order) VALUES("All users cannot proceed", "Incident", "IF 1000", 4)');
        $this->addSql('UPDATE type SET indice_factor = "IF 10", displayed_order = 2 WHERE id = 1');
        $this->addSql('UPDATE type SET displayed_order = 6 WHERE id = 2');
        $this->addSql('UPDATE type SET displayed_order = 5 WHERE id = 3');
        $this->addSql('UPDATE type SET displayed_order = 1 WHERE id = 4');
        $this->addSql('UPDATE type SET indice_factor = "IF 100", displayed_order = 3 WHERE id = 5');
        $this->addSql('UPDATE type SET displayed_order = 7 WHERE id = 6');
        $this->addSql('UPDATE type SET displayed_order = 8 WHERE id = 7');
        $this->addSql('UPDATE type SET displayed_order = 9 WHERE id = 8');
        $this->addSql('UPDATE type SET displayed_order = 11 WHERE id = 9');
        $this->addSql('UPDATE type SET displayed_order = 10 WHERE id = 10');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
