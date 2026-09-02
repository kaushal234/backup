<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210623095149 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CUSTOMER_CUSTOMER_ERP_REFERENCE_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_CUSTOMER_CUSTOMER_ERP_REFERENCE_ADMIN"
			AND user_group.name in ("SUPERUSER", "GG_SALES", "GG_SALES_AGENTS", "GG_SUPPORT", "GG_ADMIN", "ROLE_ASM", "ROLE_SA", "SALES_CUST_ADMIN", "ROLE_SPM", "ROLE_CEO", "ROLE_GCEO", "ROLE_FC")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
