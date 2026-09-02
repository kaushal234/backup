<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241024165842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add feature write on PDI';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PRE_DELIVERY_INSPECTION_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_PRE_DELIVERY_INSPECTION_WRITE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_PSM", "ROLE_ASM")');
    }

    public function down(Schema $schema): void
    {
    }
}
