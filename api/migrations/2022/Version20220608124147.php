<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220608124147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert new feature';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SCAR_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature_authorized_application (feature_id, authorized_application_id)
                   SELECT feature.id, authorized_application.id
                     FROM feature, authorized_application
                    WHERE feature.name = "FEATURE_SCAR_CREATE"
                      AND authorized_application.name = "evendors"');
    }

    public function down(Schema $schema): void
    {
    }
}
