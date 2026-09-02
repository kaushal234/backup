<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210312073906 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE wms_picking_alerts ADD quantity_picked DOUBLE PRECISION DEFAULT NULL, ADD quantity_requested DOUBLE PRECISION DEFAULT NULL, ADD inventory_unit VARCHAR(3) DEFAULT NULL, ADD stock DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE wms_picking_alerts DROP quantity_picked, DROP quantity_requested, DROP inventory_unit, DROP stock');
    }
}
