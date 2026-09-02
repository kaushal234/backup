<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181015000000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FAQ_DELETE_FILE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_FAQ_DELETE_FILE"
                        AND user_group.name in (
                            "SUPERUSER",
                            "ROLE_QAM",
                            "ROLE_CMO",
                            "ROLE_COO"
                        )
                      ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
