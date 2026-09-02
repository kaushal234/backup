<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190513081002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sales_orders DROP FOREIGN KEY FK_F52993983256915B');
        $this->addSql('ALTER TABLE sales_orders DROP FOREIGN KEY FK_F52993984269A61C');
        $this->addSql('ALTER TABLE sales_orders DROP FOREIGN KEY FK_F52993985CBDD13');
        $this->addSql('ALTER TABLE sales_orders DROP FOREIGN KEY FK_F529939879A8E92F');
        $this->addSql('ALTER TABLE sales_orders DROP FOREIGN KEY FK_F52993989C54D4BF');
        $this->addSql('ALTER TABLE sales_orders DROP FOREIGN KEY FK_F5299398DF0C0A51');
        $this->addSql('ALTER TABLE sales_orders ADD note LONGTEXT DEFAULT NULL, CHANGE juridical_location_id juridical_location_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE asm_id asm_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE buyer_id buyer_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE end_user_id end_user_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE sales_agent_id sales_agent_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE baan_customer_number baan_customer_number VARCHAR(6) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE closed_at closed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', CHANGE equote_id equote_id VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE baan_order_numbers baan_order_numbers TINYTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE customer_purchase_orders customer_purchase_orders TINYTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('DROP INDEX idx_f52993983256915b ON sales_orders');
        $this->addSql('CREATE INDEX IDX_C7DBAFE67843BFA4 ON sales_orders (sso_id)');
        $this->addSql('DROP INDEX idx_f52993985cbdd13 ON sales_orders');
        $this->addSql('CREATE INDEX IDX_C7DBAFE65CBDD13 ON sales_orders (juridical_location_id)');
        $this->addSql('DROP INDEX idx_f52993989c54d4bf ON sales_orders');
        $this->addSql('CREATE INDEX IDX_C7DBAFE69C54D4BF ON sales_orders (asm_id)');
        $this->addSql('DROP INDEX idx_f5299398df0c0a51 ON sales_orders');
        $this->addSql('CREATE INDEX IDX_C7DBAFE66C755722 ON sales_orders (buyer_id)');
        $this->addSql('DROP INDEX idx_f52993984269a61c ON sales_orders');
        $this->addSql('CREATE INDEX IDX_C7DBAFE632A1827C ON sales_orders (end_user_id)');
        $this->addSql('DROP INDEX idx_f529939879a8e92f ON sales_orders');
        $this->addSql('CREATE INDEX IDX_C7DBAFE679A8E92F ON sales_orders (sales_agent_id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_F52993983256915B FOREIGN KEY (sso_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_F52993984269A61C FOREIGN KEY (end_user_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_F52993985CBDD13 FOREIGN KEY (juridical_location_id) REFERENCES directory_juridical_location (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_F529939879A8E92F FOREIGN KEY (sales_agent_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_F52993989C54D4BF FOREIGN KEY (asm_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_F5299398DF0C0A51 FOREIGN KEY (buyer_id) REFERENCES customers (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sales_orders DROP FOREIGN KEY FK_C7DBAFE67843BFA4');
        $this->addSql('ALTER TABLE sales_orders DROP FOREIGN KEY FK_C7DBAFE65CBDD13');
        $this->addSql('ALTER TABLE sales_orders DROP FOREIGN KEY FK_C7DBAFE69C54D4BF');
        $this->addSql('ALTER TABLE sales_orders DROP FOREIGN KEY FK_C7DBAFE66C755722');
        $this->addSql('ALTER TABLE sales_orders DROP FOREIGN KEY FK_C7DBAFE632A1827C');
        $this->addSql('ALTER TABLE sales_orders DROP FOREIGN KEY FK_C7DBAFE679A8E92F');
        $this->addSql('ALTER TABLE sales_orders DROP note, CHANGE juridical_location_id juridical_location_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE asm_id asm_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE buyer_id buyer_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE end_user_id end_user_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE sales_agent_id sales_agent_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE baan_customer_number baan_customer_number VARCHAR(6) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE closed_at closed_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime)\', CHANGE equote_id equote_id VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE baan_order_numbers baan_order_numbers TINYTEXT DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\', CHANGE customer_purchase_orders customer_purchase_orders TINYTEXT DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('DROP INDEX idx_c7dbafe69c54d4bf ON sales_orders');
        $this->addSql('CREATE INDEX IDX_F52993989C54D4BF ON sales_orders (asm_id)');
        $this->addSql('DROP INDEX idx_c7dbafe679a8e92f ON sales_orders');
        $this->addSql('CREATE INDEX IDX_F529939879A8E92F ON sales_orders (sales_agent_id)');
        $this->addSql('DROP INDEX idx_c7dbafe65cbdd13 ON sales_orders');
        $this->addSql('CREATE INDEX IDX_F52993985CBDD13 ON sales_orders (juridical_location_id)');
        $this->addSql('DROP INDEX idx_c7dbafe632a1827c ON sales_orders');
        $this->addSql('CREATE INDEX IDX_F52993984269A61C ON sales_orders (end_user_id)');
        $this->addSql('DROP INDEX idx_c7dbafe67843bfa4 ON sales_orders');
        $this->addSql('CREATE INDEX IDX_F52993983256915B ON sales_orders (sso_id)');
        $this->addSql('DROP INDEX idx_c7dbafe66c755722 ON sales_orders');
        $this->addSql('CREATE INDEX IDX_F5299398DF0C0A51 ON sales_orders (buyer_id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_C7DBAFE67843BFA4 FOREIGN KEY (sso_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_C7DBAFE65CBDD13 FOREIGN KEY (juridical_location_id) REFERENCES directory_juridical_location (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_C7DBAFE69C54D4BF FOREIGN KEY (asm_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_C7DBAFE66C755722 FOREIGN KEY (buyer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_C7DBAFE632A1827C FOREIGN KEY (end_user_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_C7DBAFE679A8E92F FOREIGN KEY (sales_agent_id) REFERENCES customers (id)');
    }
}
