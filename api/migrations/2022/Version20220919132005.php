<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220919132005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing feature for NCR closed edition';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_NON_CONFORMITY_EDIT_CLOSED"
                          AND user_group.name IN ("ROLE_QE", "ROLE_QA")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
