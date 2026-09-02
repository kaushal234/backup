<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241017124256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add feature to close csr';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CUSTOMER_SERVICE_RECORD_CLOSE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_SERVICE_RECORD_CLOSE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_CSM")');
    }

    public function down(Schema $schema): void
    {
    }
}
