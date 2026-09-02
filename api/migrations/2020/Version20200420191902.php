<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200420191902 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sales_forecasts_snapshots (id INT AUTO_INCREMENT NOT NULL, original_sales_forecast_id INT DEFAULT NULL, master_sales_forecast_id INT DEFAULT NULL, sso_id INT NOT NULL, factory_id INT NOT NULL, asm_id INT NOT NULL, poster_id INT NOT NULL, buyer_id INT DEFAULT NULL, end_user_id INT DEFAULT NULL, third_party_id INT DEFAULT NULL, country_id INT DEFAULT NULL, airport_id INT DEFAULT NULL, product_id INT DEFAULT NULL, tier_id INT DEFAULT NULL, snapshot_created_at DATE NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, last_commented_at DATETIME DEFAULT NULL, status VARCHAR(255) NOT NULL, last_comment LONGTEXT DEFAULT NULL, equote_id VARCHAR(255) DEFAULT NULL, quantity INT NOT NULL, estimated_sale_date DATE NOT NULL, customer_success_percentage SMALLINT NOT NULL, success_percentage SMALLINT NOT NULL, delinquent TINYINT(1) NOT NULL, price INT DEFAULT NULL, margin DOUBLE PRECISION DEFAULT NULL, INDEX IDX_D029BA002D8D49DF (original_sales_forecast_id), INDEX IDX_D029BA00DD1FFD15 (master_sales_forecast_id), INDEX IDX_D029BA007843BFA4 (sso_id), INDEX IDX_D029BA00C7AF27D2 (factory_id), INDEX IDX_D029BA009C54D4BF (asm_id), INDEX IDX_D029BA005BB66C05 (poster_id), INDEX IDX_D029BA006C755722 (buyer_id), INDEX IDX_D029BA0032A1827C (end_user_id), INDEX IDX_D029BA0054C4149C (third_party_id), INDEX IDX_D029BA00F92F3E70 (country_id), INDEX IDX_D029BA00289F53C8 (airport_id), INDEX IDX_D029BA004584665A (product_id), INDEX IDX_D029BA00A354F9DC (tier_id), INDEX IDX_D029BA00F17B4AEB (snapshot_created_at), UNIQUE INDEX unique_subscription_per_resource (snapshot_created_at, original_sales_forecast_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sales_forecasts_snapshots ADD CONSTRAINT FK_D029BA002D8D49DF FOREIGN KEY (original_sales_forecast_id) REFERENCES sales_forecasts (id)');
        $this->addSql('ALTER TABLE sales_forecasts_snapshots ADD CONSTRAINT FK_D029BA00DD1FFD15 FOREIGN KEY (master_sales_forecast_id) REFERENCES sales_forecasts_master (id)');
        $this->addSql('ALTER TABLE sales_forecasts_snapshots ADD CONSTRAINT FK_D029BA007843BFA4 FOREIGN KEY (sso_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE sales_forecasts_snapshots ADD CONSTRAINT FK_D029BA00C7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE sales_forecasts_snapshots ADD CONSTRAINT FK_D029BA009C54D4BF FOREIGN KEY (asm_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sales_forecasts_snapshots ADD CONSTRAINT FK_D029BA005BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sales_forecasts_snapshots ADD CONSTRAINT FK_D029BA006C755722 FOREIGN KEY (buyer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE sales_forecasts_snapshots ADD CONSTRAINT FK_D029BA0032A1827C FOREIGN KEY (end_user_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE sales_forecasts_snapshots ADD CONSTRAINT FK_D029BA0054C4149C FOREIGN KEY (third_party_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE sales_forecasts_snapshots ADD CONSTRAINT FK_D029BA00F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE sales_forecasts_snapshots ADD CONSTRAINT FK_D029BA00289F53C8 FOREIGN KEY (airport_id) REFERENCES iata_codes (id)');
        $this->addSql('ALTER TABLE sales_forecasts_snapshots ADD CONSTRAINT FK_D029BA004584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE sales_forecasts_snapshots ADD CONSTRAINT FK_D029BA00A354F9DC FOREIGN KEY (tier_id) REFERENCES emission_ratings (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sales_forecasts_snapshots');
    }
}
