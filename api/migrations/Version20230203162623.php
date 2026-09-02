<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20230203162623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove POL features';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE IGNORE FROM feature_group where feature_id = (SELECT feature.id FROM feature WHERE feature.name = "FEATURE_WORK_ORDER_WRITE" );');
        $this->addSql('DELETE IGNORE FROM feature where name = "FEATURE_WORK_ORDER_WRITE"');
        $this->addSql('DELETE IGNORE FROM feature_group where feature_id = (SELECT feature.id FROM feature WHERE feature.name = "FEATURE_PRODUCTION_RESOURCE_PLANNING_WRITE" );');
        $this->addSql('DELETE IGNORE FROM feature where name = "FEATURE_PRODUCTION_RESOURCE_PLANNING_WRITE"');
        $this->addSql('DELETE IGNORE FROM feature_group where feature_id = (SELECT feature.id FROM feature WHERE feature.name = "FEATURE_MATERIAL_REQUIREMENT_PLANNING_WRITE" );');
        $this->addSql('DELETE IGNORE FROM feature where name = "FEATURE_MATERIAL_REQUIREMENT_PLANNING_WRITE"');
    }

    public function down(Schema $schema): void
    {
    }
}
