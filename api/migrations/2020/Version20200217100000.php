<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200217100000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) 	VALUES("FEATURE_TRANSPORTATION_NOTE_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_TRANSPORTATION_NOTE_WRITE"
			AND user_group.name in ( "ROLE_SA", "GG_SALES", "ROLE_BYR", "ROLE_PSM" )'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
