<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240613134904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add feature to synchronize ER from API to link.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SYNCHRONIZE_LINK_EQUIPMENT_RECORD")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SYNCHRONIZE_LINK_EQUIPMENT_RECORD"
                          AND user_group.name IN ("SUPERUSER", "LINK_INSTALLER", "LINK_ENGINEER", "LINK_COMMISSIONING")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
