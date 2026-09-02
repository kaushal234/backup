<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180124164115 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE continents (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', iso_code_2 VARCHAR(2) NOT NULL COMMENT \'(DC2Type:string)\', name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', UNIQUE INDEX UNIQ_42D1351041AE7779 (iso_code_2), UNIQUE INDEX UNIQ_42D135105E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE countries_asms (country_id INT NOT NULL COMMENT \'(DC2Type:integer)\', people_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_7A95D591F92F3E70 (country_id), INDEX IDX_7A95D5913147C936 (people_id), PRIMARY KEY(country_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE countries_asms ADD CONSTRAINT FK_7A95D591F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE countries_asms ADD CONSTRAINT FK_7A95D5913147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE countries ADD continent_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE fips_code fips_code VARCHAR(10) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE fips_name fips_name VARCHAR(100) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE latitude latitude VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE longitude longitude VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
        $this->addSql('ALTER TABLE countries ADD CONSTRAINT FK_5D66EBAD921F4C77 FOREIGN KEY (continent_id) REFERENCES continents (id)');
        $this->addSql('CREATE INDEX IDX_5D66EBAD921F4C77 ON countries (continent_id)');

        $this->addSql("INSERT INTO continents (iso_code_2, name) VALUES ('AF', 'Africa');");
        $this->addSql("INSERT INTO continents (iso_code_2, name) VALUES ('AN', 'Antarctica');");
        $this->addSql("INSERT INTO continents (iso_code_2, name) VALUES ('AS', 'Asia');");
        $this->addSql("INSERT INTO continents (iso_code_2, name) VALUES ('OC', 'Oceania');");
        $this->addSql("INSERT INTO continents (iso_code_2, name) VALUES ('EU', 'Europe');");
        $this->addSql("INSERT INTO continents (iso_code_2, name) VALUES ('NA', 'North America');");
        $this->addSql("INSERT INTO continents (iso_code_2, name) VALUES ('SA', 'South America');");

        $this->addSql("UPDATE countries SET continent_id = (SELECT id FROM continents WHERE continents.iso_code_2 = 'AF') WHERE iso_code_2 IN ('AO', 'BF', 'BI', 'BJ', 'BW', 'CD', 'CF', 'CG', 'CI', 'CM', 'CV', 'DJ', 'DZ', 'EG', 'EH', 'ER', 'ET', 'GA', 'GH', 'GM', 'GN', 'GQ', 'GW', 'KE', 'KM', 'LR', 'LS', 'LY', 'MA', 'MG', 'ML', 'MR', 'MU', 'MW', 'MZ', 'NA', 'NE', 'NG', 'RE', 'RW', 'SC', 'SD', 'SH', 'SL', 'SN', 'SO', 'ST', 'SZ', 'TD', 'TG', 'TN', 'TZ', 'UG', 'YT', 'ZA', 'ZM', 'ZW');");
        $this->addSql("UPDATE countries SET continent_id = (SELECT id FROM continents WHERE continents.iso_code_2 = 'AN') WHERE iso_code_2 IN ('AQ', 'BV', 'GS', 'HM', 'TF');");
        $this->addSql("UPDATE countries SET continent_id = (SELECT id FROM continents WHERE continents.iso_code_2 = 'AS') WHERE iso_code_2 IN ('AE', 'AF', 'AM', 'AP', 'AZ', 'BD', 'BH', 'BN', 'BT', 'CC', 'CN', 'CX', 'CY', 'GE', 'HK', 'ID', 'IL', 'IN', 'IO', 'IQ', 'IR', 'JO', 'JP', 'KG', 'KH', 'KP', 'KR', 'KW', 'KZ', 'LA', 'LB', 'LK', 'MM', 'MN', 'MO', 'MV', 'MY', 'NP', 'OM', 'PH', 'PK', 'PS', 'QA', 'SA', 'SG', 'SY', 'TH', 'TJ', 'TL', 'TM', 'TW', 'UZ', 'VN', 'YE');");
        $this->addSql("UPDATE countries SET continent_id = (SELECT id FROM continents WHERE continents.iso_code_2 = 'OC') WHERE iso_code_2 IN ('AS','AU','CK','FJ','FM','GU','KI','MH','MP','NC','NF','NR','NU','NZ','PF','PG','PN','PW','SB','TK','TO','TV','UM','VU','WF','WS');");
        $this->addSql("UPDATE countries SET continent_id = (SELECT id FROM continents WHERE continents.iso_code_2 = 'EU') WHERE iso_code_2 IN ('XK', 'AD', 'AL', 'AT', 'AX', 'BA', 'BE', 'BG', 'BY', 'CH', 'CZ', 'DE', 'DK', 'EE', 'ES', 'EU', 'FI', 'FO', 'FR', 'FX', 'GB', 'GG', 'GI', 'GR', 'HR', 'HU', 'IE', 'IM', 'IS', 'IT', 'JE', 'LI', 'LT', 'LU', 'LV', 'MC', 'MD', 'ME', 'MK', 'MT', 'NL', 'NO', 'PL', 'PT', 'RO', 'RS', 'RU', 'SE', 'SI', 'SJ', 'SK', 'SM', 'TR', 'UA', 'VA');");
        $this->addSql("UPDATE countries SET continent_id = (SELECT id FROM continents WHERE continents.iso_code_2 = 'NA') WHERE iso_code_2 IN ('AG', 'AI', 'AN', 'AW', 'BB', 'BL', 'BM', 'BS', 'BZ', 'CA', 'CR', 'CU', 'DM', 'DO', 'GD', 'GL', 'GP', 'GT', 'HN', 'HT', 'JM', 'KN', 'KY', 'LC', 'MF', 'MQ', 'MS', 'MX', 'NI', 'PA', 'PM', 'PR', 'SV', 'TC', 'TT', 'US', 'VC', 'VG', 'VI');");
        $this->addSql("UPDATE countries SET continent_id = (SELECT id FROM continents WHERE continents.iso_code_2 = 'SA') WHERE iso_code_2 IN ('CW', 'AR','BO','BR','CL','CO','EC','FK','GF','GY','PE','PY','SR','UY','VE');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE countries DROP FOREIGN KEY FK_5D66EBAD921F4C77');
        $this->addSql('DROP TABLE continents');
        $this->addSql('DROP TABLE countries_asms');
    }
}
