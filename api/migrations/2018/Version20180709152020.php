<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180709152020 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE competitor_pricings CHANGE forecast_closure_id forecast_closure_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE competitor_model competitor_model VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE quantity quantity INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE price price DOUBLE PRECISION DEFAULT NULL, CHANGE currency currency VARCHAR(10) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE exchange_rate exchange_rate DOUBLE PRECISION DEFAULT NULL, CHANGE incoterms incoterms VARCHAR(3) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE incoterms_location incoterms_location VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE markup_percentage markup_percentage SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE competitor_pricings_files CHANGE competitor_pricing_id competitor_pricing_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SALES_FORECAST_VIEW_ASM")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_COMPETITOR_PRICING_WRITE"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_ASM",
            "ROLE_EVP",
            "ROLE_CSD",
            "ROLE_COO",
            "ROLE_COO",
            "ROLE_GCOO",
            "ROLE_CEO",
            "ROLE_GCEO"
          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE competitor_pricings CHANGE forecast_closure_id forecast_closure_id INT NOT NULL COMMENT \'(DC2Type:integer)\', CHANGE competitor_model competitor_model VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE quantity quantity INT NOT NULL COMMENT \'(DC2Type:integer)\', CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE currency currency VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE exchange_rate exchange_rate DOUBLE PRECISION NOT NULL, CHANGE incoterms incoterms VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE incoterms_location incoterms_location VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE markup_percentage markup_percentage SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE competitor_pricings_files CHANGE competitor_pricing_id competitor_pricing_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
    }
}
