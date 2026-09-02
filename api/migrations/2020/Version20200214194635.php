<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200214194635 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE warehouse_locations (id INT AUTO_INCREMENT NOT NULL, sector_id INT DEFAULT NULL, erp INT NOT NULL, warehouse VARCHAR(255) NOT NULL, location_number VARCHAR(8) NOT NULL, deleted_at DATETIME DEFAULT NULL, deleted_in_baan_at DATE DEFAULT NULL, INDEX IDX_28730405DE95C867 (sector_id), UNIQUE INDEX unique_location_per_warehouse_per_erp (erp, warehouse, location_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sectors (id INT AUTO_INCREMENT NOT NULL, location_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, inbound_rate INT NOT NULL, outbound_rate INT NOT NULL, INDEX IDX_B594069864D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sector_people (sector_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_AFC91248DE95C867 (sector_id), INDEX IDX_AFC912483147C936 (people_id), PRIMARY KEY(sector_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE warehouse_locations ADD CONSTRAINT FK_28730405DE95C867 FOREIGN KEY (sector_id) REFERENCES sectors (id)');
        $this->addSql('ALTER TABLE sectors ADD CONSTRAINT FK_B594069864D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE sector_people ADD CONSTRAINT FK_AFC91248DE95C867 FOREIGN KEY (sector_id) REFERENCES sectors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sector_people ADD CONSTRAINT FK_AFC912483147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SECTOR_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SECTOR_WRITE"
                          AND user_group.name = "SUPERUSER"');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE warehouse_locations DROP FOREIGN KEY FK_28730405DE95C867');
        $this->addSql('ALTER TABLE sector_people DROP FOREIGN KEY FK_AFC91248DE95C867');
        $this->addSql('DROP TABLE warehouse_locations');
        $this->addSql('DROP TABLE sectors');
        $this->addSql('DROP TABLE sector_people');
    }
}
