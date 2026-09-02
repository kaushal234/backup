<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200424131009 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) 	VALUES("FEATURE_MARKET_INTELLIGENCE_FILE_DELETION")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_MARKET_INTELLIGENCE_FILE_DELETION"
			AND user_group.name = "SUPERUSER"'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
