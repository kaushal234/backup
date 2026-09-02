<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200319155917 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FREIGHT_FORWARDER_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SHIPPING_QUOTATION_REQUEST_VIEW")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SHIPPING_QUOTATION_REQUEST_ADMIN")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_FREIGHT_FORWARDER_ADMIN"
			AND user_group.name in ("ROLE_SA", "ROLE_SAM", "SUPERUSER")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_SHIPPING_QUOTATION_REQUEST_VIEW"
			AND user_group.name in ("SUPERUSER", "ROLE_ASM", "ROLE_SAM", "ROLE_SA")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_SHIPPING_QUOTATION_REQUEST_ADMIN"
			AND user_group.name in ("ROLE_SA", "ROLE_SAM", "SUPERUSER")'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
