<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250909083400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update EstimatedGreenTagQuantityReport properties';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE estimated_green_tag_quantity_reports_equipment_records (estimated_green_tag_quantity_report_id INT NOT NULL, equipment_record_id INT NOT NULL, INDEX IDX_A3A401734952985 (estimated_green_tag_quantity_report_id), UNIQUE INDEX UNIQ_A3A40179FC03375 (equipment_record_id), PRIMARY KEY(estimated_green_tag_quantity_report_id, equipment_record_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE estimated_green_tag_quantity_reports_equipment_records ADD CONSTRAINT FK_A3A401734952985 FOREIGN KEY (estimated_green_tag_quantity_report_id) REFERENCES estimated_green_tag_quantity_report (id)');
        $this->addSql('ALTER TABLE estimated_green_tag_quantity_reports_equipment_records ADD CONSTRAINT FK_A3A40179FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE estimated_green_tag_quantity_report DROP quantity');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE estimated_green_tag_quantity_reports_equipment_records DROP FOREIGN KEY FK_A3A401734952985');
        $this->addSql('ALTER TABLE estimated_green_tag_quantity_reports_equipment_records DROP FOREIGN KEY FK_A3A40179FC03375');
        $this->addSql('DROP TABLE estimated_green_tag_quantity_reports_equipment_records');
        $this->addSql('ALTER TABLE estimated_green_tag_quantity_report ADD quantity INT NOT NULL');
    }
}
