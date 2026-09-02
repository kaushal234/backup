<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251120110211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add estimated pick up date confirmation field on equipment shipping record line';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_shipping_record_line ADD estimated_pick_up_date_confirmation TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_shipping_record_line DROP estimated_pick_up_date_confirmation');
    }
}
