<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181107145444 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEMO_ADMIN_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_DEMO_ADMIN_EDIT"
                        AND user_group.name in (
                            "SUPERUSER"
                        )');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
