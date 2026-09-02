<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220602213822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert feature for authorized application to read people';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PEOPLE_READ")');
        $this->addSql('INSERT IGNORE INTO feature_authorized_application (feature_id, authorized_application_id)
                       SELECT feature.id, authorized_application.id
                         FROM feature, authorized_application
                        WHERE feature.name = "FEATURE_PEOPLE_READ"
                          AND authorized_application.name = "evendors"');
    }

    public function down(Schema $schema): void
    {
    }
}
