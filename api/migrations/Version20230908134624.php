<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230908134624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Currencies add FEATURE_CURRENCY_WRITE ';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES ("FEATURE_CURRENCY_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                         WHERE feature.name = "FEATURE_CURRENCY_WRITE" AND user_group.name IN ("SUPERUSER", "ERP_FOREX") ');
    }

    public function down(Schema $schema): void
    {
    }
}
