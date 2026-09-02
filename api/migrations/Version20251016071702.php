<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251016071702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Re-sync schema.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity CHANGE change_set change_set JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE metadata metadata JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE equipment_records CHANGE state state VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE pictograms_files RENAME INDEX idx_265a4a616b7c33b TO IDX_BC4CD41A16B7C33B');
        $this->addSql('ALTER TABLE power_bi_reports RENAME INDEX uniq_5f85429024689ffc TO UNIQ_5F8542909862BCB1');
        $this->addSql('ALTER TABLE power_bi_reports_groups RENAME INDEX idx_47dc43bc4bd2a4c0 TO IDX_4E6E23BE4BD2A4C0');
        $this->addSql('ALTER TABLE power_bi_reports_groups RENAME INDEX idx_47dc43bcfe54d947 TO IDX_4E6E23BEFE54D947');
        $this->addSql('ALTER TABLE report_snapshot CHANGE options options JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE x_totals x_totals JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE y_totals y_totals JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE `rows` `rows` JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE metadata metadata JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pictograms_files RENAME INDEX idx_bc4cd41a16b7c33b TO IDX_265A4A616B7C33B');
        $this->addSql('ALTER TABLE equipment_records CHANGE state state VARCHAR(50) DEFAULT \'ACTIVE\' NOT NULL');
        $this->addSql('ALTER TABLE power_bi_reports RENAME INDEX uniq_5f8542909862bcb1 TO UNIQ_5F85429024689FFC');
        $this->addSql('ALTER TABLE activity CHANGE metadata metadata JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', CHANGE change_set change_set JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE power_bi_reports_groups RENAME INDEX idx_4e6e23be4bd2a4c0 TO IDX_47DC43BC4BD2A4C0');
        $this->addSql('ALTER TABLE power_bi_reports_groups RENAME INDEX idx_4e6e23befe54d947 TO IDX_47DC43BCFE54D947');
        $this->addSql('ALTER TABLE report_snapshot CHANGE options options JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE x_totals x_totals JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE y_totals y_totals JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE `rows` `rows` JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE metadata metadata JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
