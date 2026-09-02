<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171207105823 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE iata_codes (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', country_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', city_code_3 VARCHAR(3) DEFAULT NULL COMMENT \'(DC2Type:string)\', city_name VARCHAR(60) NOT NULL COMMENT \'(DC2Type:string)\', state VARCHAR(60) DEFAULT NULL COMMENT \'(DC2Type:string)\', code VARCHAR(10) NOT NULL COMMENT \'(DC2Type:string)\', name VARCHAR(22) DEFAULT NULL COMMENT \'(DC2Type:string)\', source VARCHAR(10) NOT NULL COMMENT \'(DC2Type:string)\', type VARCHAR(60) NOT NULL COMMENT \'(DC2Type:string)\', legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', discr VARCHAR(20) NOT NULL COMMENT \'(DC2Type:string)\', INDEX IDX_5EEC8662F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE iata_codes ADD CONSTRAINT FK_5EEC8662F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE iata_codes');
    }
}
