<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180710143358 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sales_forecasts CHANGE buyer_id buyer_id INT DEFAULT NULL, CHANGE end_user_id end_user_id INT DEFAULT NULL, CHANGE product_id product_id INT DEFAULT NULL, CHANGE tier_id tier_id INT DEFAULT NULL, CHANGE master_sales_forecast_id master_sales_forecast_id INT NOT NULL');
        $this->addSql('ALTER TABLE competitor_pricings CHANGE incoterms_location incoterms_location VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
        $this->addSql('ALTER TABLE forecast_closures CHANGE price price DOUBLE PRECISION DEFAULT NULL, CHANGE currency currency VARCHAR(10) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sales_forecasts CHANGE buyer_id buyer_id INT NOT NULL, CHANGE end_user_id end_user_id INT NOT NULL, CHANGE product_id product_id INT NOT NULL, CHANGE tier_id tier_id INT NOT NULL');
        $this->addSql('ALTER TABLE competitor_pricings CHANGE incoterms_location incoterms_location VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
        $this->addSql('ALTER TABLE forecast_closures CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE currency currency VARCHAR(10) NOT NULL COMMENT \'(DC2Type:string)\'');
    }
}
