<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220912124937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'give FEATURE_COOKIE_ALVEST_SPOC to members of group GG_SPOC';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_COOKIE_ALVEST_SPOC"
			AND user_group.name = "GG_SPOC"'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
