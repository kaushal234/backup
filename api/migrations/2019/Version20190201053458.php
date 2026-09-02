<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190201053458 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sales_orders (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', sso_id INT NOT NULL COMMENT \'(DC2Type:integer)\', juridical_location_id INT COMMENT \'(DC2Type:integer)\', asm_id INT COMMENT \'(DC2Type:integer)\', buyer_id INT COMMENT \'(DC2Type:integer)\', end_user_id INT COMMENT \'(DC2Type:integer)\', sales_agent_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', baan_customer_number VARCHAR(6) DEFAULT NULL COMMENT \'(DC2Type:string)\', entered_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', closed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', status VARCHAR(20) NOT NULL COMMENT \'(DC2Type:string)\', equote_id VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\', baan_order_numbers VARCHAR(100) COMMENT \'(DC2Type:simple_array)\', customer_name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', new_customer TINYINT(1) NOT NULL, customer_purchase_orders VARCHAR(210) COMMENT \'(DC2Type:simple_array)\', xml_source LONGTEXT DEFAULT NULL, legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_F52993983256915B (sso_id), INDEX IDX_F52993985CBDD13 (juridical_location_id), INDEX IDX_F52993989C54D4BF (asm_id), INDEX IDX_F5299398DF0C0A51 (buyer_id), INDEX IDX_F52993984269A61C (end_user_id), INDEX IDX_F529939879A8E92F (sales_agent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_F52993983256915B FOREIGN KEY (sso_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_F52993985CBDD13 FOREIGN KEY (juridical_location_id) REFERENCES directory_juridical_location (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_F52993989C54D4BF FOREIGN KEY (asm_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_F5299398DF0C0A51 FOREIGN KEY (buyer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_F52993984269A61C FOREIGN KEY (end_user_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_F529939879A8E92F FOREIGN KEY (sales_agent_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE sales_orders AUTO_INCREMENT = 30000');

        $this->addSql('CREATE TABLE sales_orders_files (id INT NOT NULL COMMENT \'(DC2Type:integer)\', order_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_DBD2E2408D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sales_orders_files ADD CONSTRAINT FK_DBD2E2408D9F6D38 FOREIGN KEY (order_id) REFERENCES sales_orders (id)');
        $this->addSql('ALTER TABLE sales_orders_files ADD CONSTRAINT FK_DBD2E240BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES ("FEATURE_SALES_ORDER_READ"), ("FEATURE_SALES_ORDER_CREATE"), ("FEATURE_SALES_ORDER_EDIT"), ("FEATURE_SALES_ORDER_STATUS_UPDATE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SALES_ORDER_READ"
                          AND user_group.name IN ("SUPERUSER", "GG_ADMIN", "ROLE_SA", "ROLE_EVP", "ROLE_ASM", "GG_SUPPORT", "GG_ACCT", "GG_TRANSPORT", "GG_PARTS", "ROLE_CMO")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SALES_ORDER_CREATE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_ASM", "ROLE_EVP")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SALES_ORDER_EDIT"
                          AND user_group.name IN ("SUPERUSER","GG_ADMIN","ROLE_SA","ROLE_EVP")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SALES_ORDER_STATUS_UPDATE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_SA")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sales_orders_files');
        $this->addSql('DROP TABLE sales_orders');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name IN ("FEATURE_SALES_ORDER_READ", "FEATURE_SALES_ORDER_CREATE", "FEATURE_SALES_ORDER_EDIT", "FEATURE_SALES_ORDER_STATUS_UPDATE"))');
        $this->addSql('DELETE from feature WHERE feature.name IN ("FEATURE_SALES_ORDER_READ", "FEATURE_SALES_ORDER_CREATE", "FEATURE_SALES_ORDER_EDIT", "FEATURE_SALES_ORDER_STATUS_UPDATE")');
    }
}
