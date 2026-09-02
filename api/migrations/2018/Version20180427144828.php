<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180427144828 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE competitor_pricings (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', forecast_closure_id INT NOT NULL COMMENT \'(DC2Type:integer)\', poster_id INT NOT NULL COMMENT \'(DC2Type:integer)\', competitor_id INT NOT NULL COMMENT \'(DC2Type:integer)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', quotation_date DATE NOT NULL, competitor_model VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', competitor_options LONGTEXT DEFAULT NULL, quantity INT NOT NULL COMMENT \'(DC2Type:integer)\', price DOUBLE PRECISION NOT NULL, currency VARCHAR(10) NOT NULL COMMENT \'(DC2Type:string)\', exchange_rate DOUBLE PRECISION NOT NULL, incoterms VARCHAR(3) DEFAULT NULL COMMENT \'(DC2Type:string)\', incoterms_location VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\', markup_percentage SMALLINT DEFAULT NULL, legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_CC696AE85E107917 (forecast_closure_id), INDEX IDX_CC696AE85BB66C05 (poster_id), INDEX IDX_CC696AE878A5D405 (competitor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_forecasts_master (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_forecasts (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', master_sales_forecast_id INT NOT NULL COMMENT \'(DC2Type:integer)\', sso_id INT NOT NULL COMMENT \'(DC2Type:integer)\', factory_id INT NOT NULL COMMENT \'(DC2Type:integer)\', asm_id INT NOT NULL COMMENT \'(DC2Type:integer)\', poster_id INT NOT NULL COMMENT \'(DC2Type:integer)\', buyer_id INT NOT NULL COMMENT \'(DC2Type:integer)\', end_user_id INT NOT NULL COMMENT \'(DC2Type:integer)\', third_party_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', airport_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', product_id INT NOT NULL COMMENT \'(DC2Type:integer)\', tier_id INT NOT NULL COMMENT \'(DC2Type:integer)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', closed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', status VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', description VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', equote_id VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', quantity INT NOT NULL COMMENT \'(DC2Type:integer)\', estimated_sale_date DATE NOT NULL, customer_success_percentage SMALLINT NOT NULL, success_percentage SMALLINT NOT NULL, legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_56DB8EB3DD1FFD15 (master_sales_forecast_id), INDEX IDX_56DB8EB37843BFA4 (sso_id), INDEX IDX_56DB8EB3C7AF27D2 (factory_id), INDEX IDX_56DB8EB39C54D4BF (asm_id), INDEX IDX_56DB8EB35BB66C05 (poster_id), INDEX IDX_56DB8EB36C755722 (buyer_id), INDEX IDX_56DB8EB332A1827C (end_user_id), INDEX IDX_56DB8EB354C4149C (third_party_id), INDEX IDX_56DB8EB3289F53C8 (airport_id), INDEX IDX_56DB8EB34584665A (product_id), INDEX IDX_56DB8EB3A354F9DC (tier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE forecast_closures_files (id INT NOT NULL COMMENT \'(DC2Type:integer)\', forecast_closure_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_7205CD65E107917 (forecast_closure_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE competitor_pricings_files (id INT NOT NULL COMMENT \'(DC2Type:integer)\', competitor_pricing_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_7B038D6BB5AEE51F (competitor_pricing_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_forecasts_files (id INT NOT NULL COMMENT \'(DC2Type:integer)\', sales_forecast_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_87E1AF774B86DD53 (sales_forecast_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE forecast_closures (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', sales_forecast_id INT NOT NULL COMMENT \'(DC2Type:integer)\', poster_id INT NOT NULL COMMENT \'(DC2Type:integer)\', status VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', ordered_quantity INT NOT NULL COMMENT \'(DC2Type:integer)\', comment LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', ex_works_price DOUBLE PRECISION NOT NULL, currency VARCHAR(10) NOT NULL COMMENT \'(DC2Type:string)\', legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_B6C68D924B86DD53 (sales_forecast_id), INDEX IDX_B6C68D925BB66C05 (poster_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE competitor_pricings ADD CONSTRAINT FK_CC696AE85E107917 FOREIGN KEY (forecast_closure_id) REFERENCES forecast_closures (id)');
        $this->addSql('ALTER TABLE competitor_pricings ADD CONSTRAINT FK_CC696AE85BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE competitor_pricings ADD CONSTRAINT FK_CC696AE878A5D405 FOREIGN KEY (competitor_id) REFERENCES competitors (id)');
        $this->addSql('ALTER TABLE sales_forecasts ADD CONSTRAINT FK_56DB8EB3DD1FFD15 FOREIGN KEY (master_sales_forecast_id) REFERENCES sales_forecasts_master (id)');
        $this->addSql('ALTER TABLE sales_forecasts ADD CONSTRAINT FK_56DB8EB37843BFA4 FOREIGN KEY (sso_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE sales_forecasts ADD CONSTRAINT FK_56DB8EB3C7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE sales_forecasts ADD CONSTRAINT FK_56DB8EB39C54D4BF FOREIGN KEY (asm_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sales_forecasts ADD CONSTRAINT FK_56DB8EB35BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sales_forecasts ADD CONSTRAINT FK_56DB8EB36C755722 FOREIGN KEY (buyer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE sales_forecasts ADD CONSTRAINT FK_56DB8EB332A1827C FOREIGN KEY (end_user_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE sales_forecasts ADD CONSTRAINT FK_56DB8EB354C4149C FOREIGN KEY (third_party_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE sales_forecasts ADD CONSTRAINT FK_56DB8EB3289F53C8 FOREIGN KEY (airport_id) REFERENCES iata_codes (id)');
        $this->addSql('ALTER TABLE sales_forecasts ADD CONSTRAINT FK_56DB8EB34584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE sales_forecasts ADD CONSTRAINT FK_56DB8EB3A354F9DC FOREIGN KEY (tier_id) REFERENCES tiers (id)');
        $this->addSql('ALTER TABLE forecast_closures_files ADD CONSTRAINT FK_7205CD65E107917 FOREIGN KEY (forecast_closure_id) REFERENCES forecast_closures (id)');
        $this->addSql('ALTER TABLE forecast_closures_files ADD CONSTRAINT FK_7205CD6BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE competitor_pricings_files ADD CONSTRAINT FK_7B038D6BB5AEE51F FOREIGN KEY (competitor_pricing_id) REFERENCES competitor_pricings (id)');
        $this->addSql('ALTER TABLE competitor_pricings_files ADD CONSTRAINT FK_7B038D6BBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_forecasts_files ADD CONSTRAINT FK_87E1AF774B86DD53 FOREIGN KEY (sales_forecast_id) REFERENCES sales_forecasts (id)');
        $this->addSql('ALTER TABLE sales_forecasts_files ADD CONSTRAINT FK_87E1AF77BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE forecast_closures ADD CONSTRAINT FK_B6C68D924B86DD53 FOREIGN KEY (sales_forecast_id) REFERENCES sales_forecasts (id)');
        $this->addSql('ALTER TABLE forecast_closures ADD CONSTRAINT FK_B6C68D925BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE competitor_pricings_files DROP FOREIGN KEY FK_7B038D6BB5AEE51F');
        $this->addSql('ALTER TABLE sales_forecasts DROP FOREIGN KEY FK_56DB8EB3DD1FFD15');
        $this->addSql('ALTER TABLE sales_forecasts_files DROP FOREIGN KEY FK_87E1AF774B86DD53');
        $this->addSql('ALTER TABLE forecast_closures DROP FOREIGN KEY FK_B6C68D924B86DD53');
        $this->addSql('ALTER TABLE competitor_pricings DROP FOREIGN KEY FK_CC696AE85E107917');
        $this->addSql('ALTER TABLE forecast_closures_files DROP FOREIGN KEY FK_7205CD65E107917');
        $this->addSql('DROP TABLE competitor_pricings');
        $this->addSql('DROP TABLE sales_forecasts_master');
        $this->addSql('DROP TABLE sales_forecasts');
        $this->addSql('DROP TABLE forecast_closures_files');
        $this->addSql('DROP TABLE competitor_pricings_files');
        $this->addSql('DROP TABLE sales_forecasts_files');
        $this->addSql('DROP TABLE forecast_closures');
    }
}
