<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211210235243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Prepare DBAL 3 migration by removing deprecated type';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE activity CHANGE change_set change_set LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE activity CHANGE change_set change_set LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_bin` COMMENT \'(DC2Type:json_array)\'');
    }
}
