<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181018082104 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CUSTOMER_FILES_READ")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_FILES_READ"
                        AND user_group.name in (
                            "ROLE_GCO"
                        )');
        $this->addSql('DELETE fg
                            FROM feature_group fg
                            INNER JOIN user_group ug ON fg.group_id = ug.id
                            INNER JOIN feature f ON fg.feature_id = f.id
                            WHERE f.name = "FEATURE_CUSTOMER_FILES_READ"
                            AND ug.name = "ROLE_CFO";');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_FILES_DELETE"
                        AND user_group.name in (
                            "ROLE_LM",
                            "ROLE_GCO"
                        )');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CUSTOMER_FILES_UPLOAD")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_FILES_UPLOAD"
                        AND user_group.name in (
                            "SUPERUSER",
                            "ROLE_EVP",
                            "ROLE_LM",
                            "ROLE_GCO"
                        )');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
