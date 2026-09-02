<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250407113809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update ecust file role';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name IN ("FEATURE_CUSTOMER_FILES_READ", "FEATURE_CUSTOMER_FILES_DELETE", "FEATURE_CUSTOMER_FILES_UPLOAD")) AND group_id = 141');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name IN ("FEATURE_CUSTOMER_FILES_READ", "FEATURE_CUSTOMER_FILES_DELETE", "FEATURE_CUSTOMER_FILES_UPLOAD")
                          AND user_group.name = "ROLE_LCM"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
