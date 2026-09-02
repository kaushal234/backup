<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210302094916 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE outbound_requests (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, production_order INT NOT NULL, operation INT NOT NULL, project VARCHAR(6) NOT NULL, created_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, baan_operation_starting_date DATE NOT NULL, status VARCHAR(25) NOT NULL, INDEX IDX_2DF1F99264D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE outbound_requests ADD CONSTRAINT FK_2DF1F99264D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE outbound_requests');
    }
}
