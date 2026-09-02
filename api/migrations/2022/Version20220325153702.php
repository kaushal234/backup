<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220325153702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add specific feature for evendors authorized app to read planned MRPs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_VENDOR_USER_IMPERSONATE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_VENDOR_USER_IMPERSONATE"
                          AND user_group.name IN ("SUPERUSER", "GG_PUR", "GG_TRANSPORT", "ROLE_SA", "GG_PARTS", "VENDORS")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
