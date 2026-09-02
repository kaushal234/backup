<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171228072600 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CUSTOMER_FILES_READ")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CUSTOMER_FILES_DELETE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_FILES_READ"
                        AND user_group.name in (
                          "SUPERUSER",
                          "ROLE_GCEO",
                          "ROLE_GCOO",
                          "ROLE_LM",
                          "ROLE_CFO"
                        )'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_FILES_DELETE"
                        AND user_group.name in (
                          "SUPERUSER"
                        )'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
