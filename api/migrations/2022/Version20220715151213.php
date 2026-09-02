<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20220715151213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add feature for authorized to edit VWC';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES ("FEATURE_VENDOR_WARRANTY_CLAIM_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_authorized_application (feature_id, authorized_application_id)
                   SELECT feature.id, authorized_application.id
                     FROM feature, authorized_application
                    WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_WRITE"
                      AND authorized_application.name = "evendors"');
    }
}
