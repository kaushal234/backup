<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250704133041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing components.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO equipment_serial_components (name) VALUES ("CANOPY")');
        $this->addSql('INSERT IGNORE INTO equipment_serial_components (name) VALUES ("CAMERA")');
        $this->addSql('INSERT IGNORE INTO equipment_serial_components (name) VALUES ("FLIGHT")');
        $this->addSql('INSERT IGNORE INTO equipment_serial_components (name) VALUES ("ADVEEZ")');
        $this->addSql('INSERT IGNORE INTO equipment_serial_components (name) VALUES ("STABILIZER")');
        $this->addSql('INSERT IGNORE INTO equipment_serial_components (name) VALUES ("PLATFORM")');
        $this->addSql('INSERT IGNORE INTO equipment_serial_components (name) VALUES ("EMETOR")');
        $this->addSql('INSERT IGNORE INTO equipment_serial_components (name) VALUES ("RECEPTOR")');
        $this->addSql('INSERT IGNORE INTO equipment_serial_components (name) VALUES ("REMOTE")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
