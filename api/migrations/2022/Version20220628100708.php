<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220628100708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert new feature for authorized app PIO';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PEOPLE_ITEM_READ")');
        $this->addSql('INSERT IGNORE INTO feature_authorized_application (feature_id, authorized_application_id)
                   SELECT feature.id, authorized_application.id
                     FROM feature, authorized_application
                    WHERE feature.name = "FEATURE_PEOPLE_ITEM_READ"
                      AND authorized_application.name = "PIO"');
    }

    public function down(Schema $schema): void
    {
    }
}
