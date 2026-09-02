<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260330121444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add locations collection to Power BI reports';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE power_bi_reports_locations (report_id INT NOT NULL, location_id INT NOT NULL, INDEX IDX_A5F35A2F4BD2A4C0 (report_id), INDEX IDX_A5F35A2F64D218E (location_id), PRIMARY KEY(report_id, location_id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE power_bi_reports_locations ADD CONSTRAINT FK_A5F35A2F4BD2A4C0 FOREIGN KEY (report_id) REFERENCES power_bi_reports (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE power_bi_reports_locations ADD CONSTRAINT FK_A5F35A2F64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE power_bi_reports_locations DROP FOREIGN KEY FK_A5F35A2F4BD2A4C0');
        $this->addSql('ALTER TABLE power_bi_reports_locations DROP FOREIGN KEY FK_A5F35A2F64D218E');
        $this->addSql('DROP TABLE power_bi_reports_locations');
    }
}
