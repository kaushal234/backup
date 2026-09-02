<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200901074428 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DELETE IGNORE FROM feature_group where feature_id = (SELECT feature.id FROM feature WHERE feature.name = "FEATURE_MANUFACTURING_MARGIN_VIEW_EDIT" );');
        $this->addSql('DELETE IGNORE FROM feature where name = "FEATURE_MANUFACTURING_MARGIN_VIEW_EDIT"');

        $this->addSql('DELETE IGNORE FROM feature_group where feature_id = (SELECT feature.id FROM feature WHERE feature.name = "FEATURE_MANUFACTURING_MARGIN_VIEW_CREATE" );');
        $this->addSql('DELETE IGNORE FROM feature where name = "FEATURE_MANUFACTURING_MARGIN_VIEW_CREATE"');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MANUFACTURING_MARGIN_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MANUFACTURING_MARGIN_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MANUFACTURING_MARGIN_VIEW_FULL")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MANUFACTURING_MARGIN_VIEW_LOCATION")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MANUFACTURING_MARGIN_CREATE"
          AND user_group.name IN ("ROLE_SPM", "SUPERUSER", "GG_ADMIN", "ROLE_CFO", "ROLE_FC", "ROLE_MPE", "ROLE_PS", "ROLE_PM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MANUFACTURING_MARGIN_EDIT"
          AND user_group.name IN ("SUPERUSER", "GG_ADMIN", "ROLE_CFO")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MANUFACTURING_MARGIN_VIEW_FULL"
          AND user_group.name IN ("SUPERUSER", "GG_ADMIN", "ROLE_CFO", "ROLE_COO", "ROLE_RCOO", "ROLE_RCEO", "ROLE_PM", "ROLE_GTD")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MANUFACTURING_MARGIN_VIEW_LOCATION"
          AND user_group.name IN ("ROLE_PS", "ROLE_FC", "ROLE_MPE")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
