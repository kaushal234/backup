<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200115165812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create FEATURE_ARCHIVE_DOWNLOAD and assign it to user groups';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT IGNORE INTO feature (name) VALUES('FEATURE_ARCHIVE_DOWNLOAD')");
        $this->addSql("INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = 'FEATURE_ARCHIVE_DOWNLOAD'
			AND user_group.name in (
			    'SUPERUSER',
                'gg_ADMIN',
                'gg_PUR',
                'gg_SALES',
                'gg_PARTS',
                'gg_SUPPORT',
                'gg_ACCT',
                'role_COO'
			)"
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
