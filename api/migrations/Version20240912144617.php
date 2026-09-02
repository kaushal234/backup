<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20240912144617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'allow finance department to edit CSR';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_SERVICE_RECORD_EDIT"
                          AND user_group.name IN ("GG_ACCT")');
    }

    public function down(Schema $schema): void
    {
    }
}
