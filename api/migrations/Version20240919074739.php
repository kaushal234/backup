<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919074739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove permission from HR to admin positions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("DELETE feature_group FROM feature_group
            LEFT JOIN feature on feature_group.feature_id = feature.id
            LEFT JOIN user_group on feature_group.group_id = user_group.id
            WHERE user_group.name IN ('GG_HR')
            AND feature.name = 'FEATURE_POSITION_WRITE'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
