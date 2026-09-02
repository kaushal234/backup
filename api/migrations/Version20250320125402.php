<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250320125402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update CRAB delete rules';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CRAB_TEST_ASSY_DELETE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CRAB_TEST_ASSY_DELETE"
                          AND user_group.name IN ("ROLE_PS", "ROLE_PM")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name IN ("FEATURE_CRAB_TEST_DELETE", "FEATURE_CRAB_ASSY_DELETE"))');
        $this->addSql('DELETE from feature WHERE feature.name IN ("FEATURE_CRAB_TEST_DELETE", "FEATURE_CRAB_ASSY_DELETE")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
