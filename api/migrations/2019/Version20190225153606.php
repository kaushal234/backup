<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190225153606 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE countries ADD public TINYINT(1) NOT NULL DEFAULT 1, CHANGE continent_id continent_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE fips_code fips_code VARCHAR(10) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE fips_name fips_name VARCHAR(100) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE latitude latitude VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE longitude longitude VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE countries DROP public, CHANGE continent_id continent_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE fips_code fips_code VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE fips_name fips_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE latitude latitude VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE longitude longitude VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
    }
}
