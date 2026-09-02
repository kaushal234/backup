<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190503193421 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_CATALOG_TYPE_EDIT"
			AND user_group.name in ( "ROLE_GTD" )');

        $this->addSql('DELETE feature_group FROM feature_group
            LEFT JOIN feature on feature_group.feature_id = feature.id
            LEFT JOIN user_group on feature_group.group_id = user_group.id
            WHERE user_group.name IN ("ROLE_PSM", "ROLE_PSA", "ROLE_PSE", "GG_ADMIN")
            AND feature.name = "FEATURE_CATALOG_TYPE_EDIT"');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CATALOG_TYPE_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_CATALOG_TYPE_CREATE"
			AND user_group.name in ( "ROLE_GTD", "SUPERUSER" )');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
