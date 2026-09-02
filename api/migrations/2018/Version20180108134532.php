<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180108134532 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B56089BF;');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B56089BF FOREIGN KEY (extranet_user_profile_id) REFERENCES extranet_user_profile (id) ON DELETE CASCADE;\');');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_CREATE"
                        AND user_group.name in (
                          "ROLE_CEO",
                          "ROLE_GCEO",
                          "GG_SALES_AGENTS"
                        )'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_EDIT"
                        AND user_group.name in (
                          "ROLE_CEO",
                          "ROLE_GCEO",
                          "SALES_CUST_ADMIN"
                        )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
