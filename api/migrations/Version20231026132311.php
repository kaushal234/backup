<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231026132311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update type of property notes on equipment shipping record';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_shipping_record CHANGE notes notes LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_shipping_record CHANGE notes notes VARCHAR(255) DEFAULT NULL');
    }
}
