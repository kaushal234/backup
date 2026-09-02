<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20220705011244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove LM and SAM from FEATURE_CUSTOMER_ADMIN';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE IGNORE FROM feature_group where feature_id = (SELECT feature.id FROM feature WHERE feature.name = "FEATURE_CUSTOMER_ADMIN") and group_id != (SELECT user_group.id FROM user_group WHERE user_group.name = "SUPERUSER");');
    }

    public function down(Schema $schema): void
    {
    }
}
