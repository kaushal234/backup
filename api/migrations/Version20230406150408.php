<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230406150408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'give feature FEATURE_CUSTOMER_FILES_READ to RCFO and Finance Controllers with role ROLE_CFO and role ROLE_FC';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_CUSTOMER_FILES_READ"
          AND user_group.name in ("ROLE_CFO", "ROLE_FC")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
