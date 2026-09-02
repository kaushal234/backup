<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007071243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update uniqueness on estimated_green_tag_quantity_reports relation with ER';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE estimated_green_tag_quantity_reports_equipment_records DROP INDEX UNIQ_A3A40179FC03375, ADD INDEX IDX_A3A40179FC03375 (equipment_record_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE estimated_green_tag_quantity_reports_equipment_records DROP INDEX IDX_A3A40179FC03375, ADD UNIQUE INDEX UNIQ_A3A40179FC03375 (equipment_record_id)');
    }
}
