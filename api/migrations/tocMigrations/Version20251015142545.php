<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015142545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC Zone done and cp';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE technician_on_call_zone (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, delay INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technician_on_call_zone_country (zone_id INT NOT NULL, country_id INT NOT NULL, INDEX IDX_791F7C169F2C3FAB (zone_id), UNIQUE INDEX UNIQ_791F7C16F92F3E70 (country_id), PRIMARY KEY(zone_id, country_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE technician_on_call_zone_country ADD CONSTRAINT FK_791F7C169F2C3FAB FOREIGN KEY (zone_id) REFERENCES technician_on_call_zone (id)');
        $this->addSql('ALTER TABLE technician_on_call_zone_country ADD CONSTRAINT FK_791F7C16F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');

        $this->addSql("INSERT INTO technician_on_call_zone (name, delay) VALUES ('G', 48)");
        $this->addSql("INSERT INTO technician_on_call_zone (name, delay) VALUES ('B', 72)");
        $this->addSql("INSERT INTO technician_on_call_zone (name, delay) VALUES ('Y', 120)");
        $this->addSql("INSERT INTO technician_on_call_zone (name, delay) VALUES ('R', 168)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call_zone_country DROP FOREIGN KEY FK_791F7C169F2C3FAB');
        $this->addSql('ALTER TABLE technician_on_call_zone_country DROP FOREIGN KEY FK_791F7C16F92F3E70');
        $this->addSql('DROP TABLE technician_on_call_zone');
        $this->addSql('DROP TABLE technician_on_call_zone_country');
    }
}
