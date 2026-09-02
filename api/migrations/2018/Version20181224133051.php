<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181224133051 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FINANCE_FAMILY_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FINANCE_FAMILY_PRICING_READ")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FINANCE_FAMILY_PRICING_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_COMPETITOR_DELETE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_CFO", "ROLE_FC")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_FINANCE_FAMILY_PRICING_READ"
                          AND user_group.name IN ("SUPERUSER", "ROLE_CFO", "ROLE_FC")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_FINANCE_FAMILY_PRICING_ADMIN"
                          AND user_group.name IN ("SUPERUSER", "ROLE_CFO", "ROLE_FC")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
