<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211126215411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Implement entity printer and feature printer write';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE manual_printers (id INT AUTO_INCREMENT NOT NULL, company_name VARCHAR(255) NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, address_country VARCHAR(2) DEFAULT NULL, address_street1 VARCHAR(255) DEFAULT NULL, address_street2 VARCHAR(255) DEFAULT NULL, address_postal_code VARCHAR(20) DEFAULT NULL, address_city VARCHAR(50) DEFAULT NULL, address_town VARCHAR(50) DEFAULT NULL, address_state VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT IGNORE INTO manual_printers (company_name, firstname, lastname, email) VALUES("GARRIGUES DESIGN GRAPHIQUE", "Sylvain", "Garrigues", "webmaster@sylvain-garrigues.com")');
        $this->addSql('INSERT IGNORE INTO manual_printers (company_name, firstname, lastname, email) VALUES("Sir Speedy", "Desiree", "Jacobs", "djacobs@printmarkservices.com")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PRINTER_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_PRINTER_WRITE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_PSM", "ROLE_PSE", "ROLE_PSA")');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE manual_printers');
    }
}
