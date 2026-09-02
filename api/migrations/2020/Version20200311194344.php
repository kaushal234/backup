<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200311194344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new fiels on customer and insert related user feature';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CUSTOMER_WATCH_LIST")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_CUSTOMER_WATCH_LIST"
			AND user_group.name in ("ROLE_SA", "ROLE_EVP", "SUPERUSER")'
        );

        $this->addSql('ALTER TABLE customers ADD watch_list TINYINT(1) NOT NULL, ADD watch_list_reason VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customers DROP watch_list, DROP watch_list_reason');
    }
}
