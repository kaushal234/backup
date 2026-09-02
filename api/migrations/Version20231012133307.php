<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231012133307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update type of property fixing_comments and inspecting_comments in CRABS';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE crab CHANGE fixing_comments fixing_comments LONGTEXT DEFAULT NULL, CHANGE inspecting_comments inspecting_comments LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE crab CHANGE fixing_comments fixing_comments VARCHAR(255) DEFAULT NULL, CHANGE inspecting_comments inspecting_comments VARCHAR(255) DEFAULT NULL');
    }
}
