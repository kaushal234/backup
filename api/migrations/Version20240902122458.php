<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240902122458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow SMART AIRPORT SYSTEM to access Parts Dashboard';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_sub_division (sub_division_id, feature_id)
                        SELECT directory_sub_division.id, feature.id
                        FROM directory_sub_division, feature
                        WHERE feature.name = "FEATURE_PARTS_DASHBOARD_FULL_VIEW"
                        AND directory_sub_division.name IN ("SMART AIRPORT SYSTEMS")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
