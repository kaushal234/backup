<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201109160618 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE warehouse_monthly_activities (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, applicated_on DATE NOT NULL, inbound_lines INT NOT NULL, outbound_lines INT NOT NULL, inbound_hours INT NOT NULL, outbound_hours INT NOT NULL, improductive_hours INT NOT NULL, excluded_hours INT NOT NULL, INDEX IDX_9FFAFC1064D218E (location_id), UNIQUE INDEX unique_activities_perlocation_per_month (location_id, applicated_on), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE warehouse_tasks_mappings (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, inbound_tasks TINYTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', outbound_tasks TINYTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', administrative_tasks TINYTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', excluded_tasks TINYTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', UNIQUE INDEX unique_location (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE warehouse_monthly_activities ADD CONSTRAINT FK_9FFAFC1064D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE warehouse_tasks_mappings ADD CONSTRAINT FK_58FBC89A64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE warehouse_monthly_activities');
        $this->addSql('DROP TABLE warehouse_tasks_mappings');
    }
}
