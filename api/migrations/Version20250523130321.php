<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250523130321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add features for Event module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_HOLIDAYS_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_HOLIDAYS_ADMIN"
                          AND user_group.name IN ("HOLIDAYS")')
        ;
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
