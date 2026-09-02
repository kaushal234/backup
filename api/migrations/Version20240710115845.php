<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240710115845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add feature to read powerbi reports of AES';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_POWERBI_AES_READ")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_POWERBI_AES_READ"
          AND user_group.name IN (
              "ROLE_GCEO",
              "ROLE_TCEO",
              "ROLE_GCFO",
              "ROLE_CEO",
              "ROLE_COO",
              "SUPERUSER")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
