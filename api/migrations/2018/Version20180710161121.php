<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180710161121 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_COMPETITOR_PRICING_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_COMPETITOR_PRICING_WRITE"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_ASM",
            "ROLE_EVP",
            "ROLE_CSD",
            "ROLE_COO",
            "ROLE_COO",
            "ROLE_GCOO",
            "ROLE_CEO",
            "ROLE_GCEO"
          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
