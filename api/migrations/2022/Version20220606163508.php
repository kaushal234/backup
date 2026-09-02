<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220606163508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add authorized app features and automatically add them on the evendors app';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PLANNED_MRP_READ"), ("FEATURE_LOCATIONS_READ")');

        foreach ([
            'FEATURE_DMS_READ',
            'FEATURE_RFQ_READ',
            'FEATURE_ERP_PURCHASE_ORDERS_READ',
            'FEATURE_PURCHASE_CONFIRM_DATE_WRITE',
            'FEATURE_COMMENT_READ',
            'FEATURE_COMMENT_WRITE',
            'FEATURE_PLANNED_MRP_READ',
            'FEATURE_LOCATIONS_READ',
        ] as $feature) {
            $this->addSql(\sprintf('INSERT IGNORE INTO feature_authorized_application (feature_id, authorized_application_id)
                       SELECT feature.id, authorized_application.id
                         FROM feature, authorized_application
                        WHERE feature.name = "%s"
                          AND authorized_application.name = "evendors"', $feature));
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
