<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190718133609 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_CUSTOMER_DELETE"
			AND user_group.name in ( "ROLE_SA", "ROLE_EVP", "SALES_CUST_ADMIN", "ROLE_CEO", "role_gceo" )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
