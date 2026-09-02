<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260108082833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC add token done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call ADD token LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE technician_on_call_backlog_report CHANGE date date DATE NOT NULL');
        $this->addSql('ALTER TABLE technician_on_call_oldest_report CHANGE date date DATE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call_oldest_report CHANGE date date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE technician_on_call_backlog_report CHANGE date date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE technician_on_call DROP token');
    }
}
