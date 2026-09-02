<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250718130951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Give QAM permissions to see all Supplier Rankings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name IN ("FEATURE_SUPPLIER_RANKING_READ")) AND group_id = 35');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SUPPLIER_RANKING_READ_ALL"
          AND user_group.name in ("ROLE_QAM")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
