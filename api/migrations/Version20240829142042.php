<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20240829142042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Gives right to upload Customers files to every role allowed to edit customers';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_CUSTOMER_FILES_UPLOAD"
          AND user_group.name in ("ROLE_ASM", "GG_ADMIN", "ROLE_CEO", "ROLE_SA", "SALES_CUST_ADMIN", "ROLE_SAM", "ROLE_CSD", "role_gceo")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
