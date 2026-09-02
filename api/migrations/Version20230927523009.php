<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20230927523009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add permission to edit CRAB fixingComments for GG_QUALITY, ROLE_QAM, ROLE_PM and SUPERUSER';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CRAB_EDIT_FIX")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CRAB_EDIT_FIX"
                          AND user_group.name IN ("GG_QUALITY", "ROLE_QAM", "ROLE_PM", "SUPERUSER")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
