<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250423063426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add Powerbi read feature for new report 58';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_POWERBI_MATERIALS_PURCHASE_READ")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_POWERBI_MATERIALS_PURCHASE_READ"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_CFO",
            "ROLE_CPO",
            "ROLE_BYR",
            "ROLE_MLM",
            "ROLE_COO",
            "ROLE_RCEO",
            "ROLE_TCEO",
            "ROLE_TCOO",
            "ROLE_GCEO"
          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
