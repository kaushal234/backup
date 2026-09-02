<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251009064609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow GG_TRANSPORT to edit ODP as support';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_ODP_EDIT_SUPPORT"
          AND user_group.name in ("GG_TRANSPORT")'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
