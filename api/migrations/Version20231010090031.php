<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231010090031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add permission to inspect CRAB QA for GG_QUALITY, ROLE_QAM and SUPERUSER';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CRAB_INSPECT_QA")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CRAB_INSPECT_QA"
                          AND user_group.name IN ("GG_QUALITY", "ROLE_QAM", "SUPERUSER")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
