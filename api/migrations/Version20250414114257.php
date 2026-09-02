<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414114257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add role SPM to access supplier ranking in read only';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SUPPLIER_RANKING_READ")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SUPPLIER_RANKING_READ"
                          AND user_group.name IN ("ROLE_SPM")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SUPPLIER_RANKING_READ_ALL")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SUPPLIER_RANKING_READ_ALL"
                          AND user_group.name IN ("ROLE_SPM")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SUPPLIER_RANKING_UPDATE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SUPPLIER_RANKING_UPDATE"
                          AND user_group.name IN ("ROLE_SPM")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SUPPLIER_RANKING_CLASSIFICATION_READ")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SUPPLIER_RANKING_CLASSIFICATION_READ"
                          AND user_group.name IN ("ROLE_SPM")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SUPPLIER_RANKING_EXPERTISE_LEVEL_READ")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SUPPLIER_RANKING_EXPERTISE_LEVEL_READ"
                          AND user_group.name IN ("ROLE_SPM")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SUPPLIER_RANKING_CRITERIA_READ")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SUPPLIER_RANKING_CRITERIA_READ"
                          AND user_group.name IN ("ROLE_SPM")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
