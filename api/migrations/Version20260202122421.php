<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260202122421 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'New feature for edit pick up confirmation on ESR line';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_EQUIPMENT_SHIPPING_RECORD_LINE_PICK_UP_CONFIRMATION', ['ROLE_PSM', 'ROLE_PSE', 'ROLE_PSA']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
