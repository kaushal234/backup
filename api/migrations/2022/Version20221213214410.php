<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221213214410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update groups granted with feature FEATURE_EVENDORS_NEWS_WRITE';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM feature_group
                        WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_EVENDORS_NEWS_WRITE")
                        AND group_id IN (SELECT user_group.id from user_group WHERE user_group.name = "ROLE_BYR")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_EVENDORS_NEWS_WRITE"
                        AND user_group.name IN ("ROLE_COO", "ROLE_CPO")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
