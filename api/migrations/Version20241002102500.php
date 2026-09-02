<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241002102500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CSR files visibility change features';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CSR_FILE_CHANGE_VISIBILITY")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CSR_FILE_CHANGE_VISIBILITY"
                          AND user_group.name IN ("SUPERUSER", "ROLE_AST", "ROLE_CSM", "ROLE_EVP", "GG_SERVICE")');
    }

    public function down(Schema $schema): void
    {
    }
}
