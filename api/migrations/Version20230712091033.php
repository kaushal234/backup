<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230712091033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add FEATURE_SPARE_PARTS_REQUESTS_SHIPPED_TO_CLOSE for authorize AST and CSM to close a shipped SPR';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SPARE_PARTS_REQUESTS_SHIPPED_TO_CLOSE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SPARE_PARTS_REQUESTS_SHIPPED_TO_CLOSE"
                          AND user_group.name IN ("ROLE_AST", "ROLE_CSM", "GG_PARTS", "GG_PARTS_AGENTS")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
