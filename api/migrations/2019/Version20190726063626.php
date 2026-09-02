<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190726063626 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE emission_ratings ADD obsolete TINYINT(1) NOT NULL');

        $this->addSql('INSERT IGNORE INTO feature (name) 	VALUES("FEATURE_EMISSION_RATING_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_EMISSION_RATING_WRITE"
			AND user_group.name  = "SUPERUSER"'
        );
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE emission_ratings DROP obsolete');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_EMISSION_RATING_WRITE")');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_EMISSION_RATING_WRITE"');
    }
}
