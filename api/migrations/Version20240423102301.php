<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240423102301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CSR features';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CUSTOMER_SERVICE_RECORD_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CUSTOMER_SERVICE_RECORD_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CUSTOMER_SERVICE_RECORD_DELETE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CUSTOMER_SERVICE_RECORD_PLANNER")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_SERVICE_RECORD_EDIT"
                          AND user_group.name IN ("SUPERUSER", "ROLE_AST", "ROLE_CSM", "ROLE_EVP", "GG_SERVICE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_SERVICE_RECORD_CREATE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_AST", "ROLE_CSM", "ROLE_EVP", "GG_SERVICE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_SERVICE_RECORD_DELETE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_CSM", "ROLE_EVP", "GG_SERVICE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_SERVICE_RECORD_PLANNER"
                          AND user_group.name IN ("SUPERUSER", "ROLE_AST", "ROLE_CSM", "ROLE_EVP", "GG_SERVICE", "ROLE_ASM")');
    }

    public function down(Schema $schema): void
    {
    }
}
