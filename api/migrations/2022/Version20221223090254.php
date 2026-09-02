<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221223090254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'give feature FEATURE_PRINTER_WRITE to buyers with role ROLE_BYR';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_PRINTER_WRITE"
                          AND user_group.name = "ROLE_BYR"');
    }

    public function down(Schema $schema): void
    {
    }
}
