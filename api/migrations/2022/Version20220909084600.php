<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220909084600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing permissions on VWC';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_EDIT"
                          AND user_group.name IN ("ROLE_QE", "GG_QUALITY")');

        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_EDIT_PARTS")');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_EDIT_PARTS"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
