<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260701135543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create service_areas and service_areas_airports tables to manage Service Areas (named groups of airports with a representative) for TOC filtering';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE service_areas (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, representative_id INT NOT NULL, INDEX IDX_F1C25FBBFC3FF006 (representative_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE service_areas_airports (service_area_id INT NOT NULL, airport_id INT NOT NULL, INDEX IDX_83A14334728B1200 (service_area_id), INDEX IDX_83A14334289F53C8 (airport_id), PRIMARY KEY(service_area_id, airport_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE service_areas ADD CONSTRAINT FK_F1C25FBBFC3FF006 FOREIGN KEY (representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE service_areas_airports ADD CONSTRAINT FK_83A14334728B1200 FOREIGN KEY (service_area_id) REFERENCES service_areas (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service_areas_airports ADD CONSTRAINT FK_83A14334289F53C8 FOREIGN KEY (airport_id) REFERENCES iata_codes (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service_areas DROP FOREIGN KEY FK_F1C25FBBFC3FF006');
        $this->addSql('ALTER TABLE service_areas_airports DROP FOREIGN KEY FK_83A14334728B1200');
        $this->addSql('ALTER TABLE service_areas_airports DROP FOREIGN KEY FK_83A14334289F53C8');
        $this->addSql('DROP TABLE service_areas');
        $this->addSql('DROP TABLE service_areas_airports');
    }
}
