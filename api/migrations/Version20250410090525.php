<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410090525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add two features to allow PSM, PSA and PSE to add DMS in catalog.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CATALOG_PRODUCT_ADD_DMS")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CATALOG_FAMILY_ADD_DMS")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CATALOG_PRODUCT_ADD_DMS"
                          AND user_group.name IN ("SUPERUSER", "ROLE_PSM", "ROLE_PSA", "ROLE_PSE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CATALOG_FAMILY_ADD_DMS"
                          AND user_group.name IN ("SUPERUSER", "ROLE_PSM", "ROLE_PSA", "ROLE_PSE")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
