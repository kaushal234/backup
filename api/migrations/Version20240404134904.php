<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240404134904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add applications and modules';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(1, "ERP LN")');
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(2, "ERP EAM SUN")');
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(3, "LINK")');
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(4, "ENG applications")');
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(5, "Desk Applications")');
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(6, "Intranet")');
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(7, "End user device")');
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(8, "HR applications")');
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(9, "Finance Applications")');
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(10, "Evendor")');
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(11, "Shopfloor")');
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(12, "Extranet")');
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(13, "External Websites")');
        $this->addSql('INSERT IGNORE INTO application (id, name) VALUES(14, "Network")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
