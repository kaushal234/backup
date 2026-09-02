<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220610164319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert new feature for Authorized App';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SHIPPED_SALES_ORDERS_NOTIFY")');
        $this->addSql('INSERT IGNORE INTO feature_authorized_application (feature_id, authorized_application_id)
                   SELECT feature.id, authorized_application.id
                     FROM feature, authorized_application
                    WHERE feature.name = "FEATURE_SHIPPED_SALES_ORDERS_NOTIFY"
                      AND authorized_application.name = "ION"');
    }

    public function down(Schema $schema): void
    {
    }
}
