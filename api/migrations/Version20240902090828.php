<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240902090828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make possible edition of QUALIFIED FAQ for QAM';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FIRST_ARTICLE_QUALIFICATION_EDIT_QUALIFIED")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_FIRST_ARTICLE_QUALIFICATION_EDIT_QUALIFIED"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_QAM"
          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
