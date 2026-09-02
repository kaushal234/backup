<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250717085326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC - Add features done and cp';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TECHNICIAN_ON_CALL_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_TECHNICIAN_ON_CALL_EDIT"
                          AND user_group.name IN ("GG_SERVICE", "ROLE_CSM", "ROLE_EVP", "SUPERUSER", "ROLE_CSD")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TECHNICIAN_ON_CALL_OPEN_FACTORY_FLAG")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_TECHNICIAN_ON_CALL_OPEN_FACTORY_FLAG"
                          AND user_group.name IN ("GG_SERVICE", "ROLE_CSM", "ROLE_EVP", "SUPERUSER", "ROLE_CSD", "ROLE_AST")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TECHNICIAN_ON_CALL_CLOSE_FACTORY_FLAG")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_TECHNICIAN_ON_CALL_CLOSE_FACTORY_FLAG"
                          AND user_group.name IN ("GG_SERVICE", "ROLE_CSM", "ROLE_EVP", "SUPERUSER", "ROLE_CSD", "ROLE_AST", "ROLE_PSE", "ROLE_PSA", "ROLE_PSM", "ROLE_RME", "GG_ENG")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TECHNICIAN_ON_CALL_SUSPENDED")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_TECHNICIAN_ON_CALL_SUSPENDED"
                          AND user_group.name IN ("ROLE_CSM", "SUPERUSER", "ROLE_CSD")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
