<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230327100058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'give feature FEATURE_PRODUCT_CERTIFICATE_ADMIN to TSM with role ROLE_TSM';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_PRODUCT_CERTIFICATE_ADMIN"
          AND user_group.name = "ROLE_TSM"'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
