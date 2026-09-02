<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200318092049 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records ADD estimated_green_tag_date DATETIME DEFAULT NULL, ADD odp_comment LONGTEXT DEFAULT NULL');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_EQUIPMENT_RECORD_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_EQUIPMENT_RECORD_EDIT"
			AND user_group.name in ( "ROLE_CMO", "SUPERUSER" )'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records DROP estimated_green_tag_date, DROP odp_comment');
    }
}
