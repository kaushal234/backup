<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241025120659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add feature to update disabledForTroubleTickets property in module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MODULE_DISABLED_TROUBLE_TICKET_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MODULE_DISABLED_TROUBLE_TICKET_WRITE"
          AND user_group.name in ("SUPERUSER", "GG_MIS")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
