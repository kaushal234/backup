<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20240416090826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove Approvers feature';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_FINANCE_APPROVER")');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_FINANCE_APPROVER"');
    }

    public function down(Schema $schema): void
    {
    }
}
