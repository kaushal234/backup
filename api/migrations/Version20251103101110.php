<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251103101110 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Create Aircraft and Aircraft Compatibility entities';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aircraft (id INT AUTO_INCREMENT NOT NULL, manufacturer_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_13D96729A23B42D (manufacturer_id), UNIQUE INDEX unique_aircraft_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE aircraft_compatibility (id INT AUTO_INCREMENT NOT NULL, legacy_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE aircraft_compatibility_product (aircraft_compatibility_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_5BFA582D68C32ABE (aircraft_compatibility_id), INDEX IDX_5BFA582D4584665A (product_id), PRIMARY KEY(aircraft_compatibility_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE aircraft_compatibility_aircraft (aircraft_compatibility_id INT NOT NULL, aircraft_id INT NOT NULL, INDEX IDX_FEE9545568C32ABE (aircraft_compatibility_id), INDEX IDX_FEE95455846E2F5C (aircraft_id), PRIMARY KEY(aircraft_compatibility_id, aircraft_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE aircraft_compatibility_files (id INT NOT NULL, aircraft_compatibility_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_BDF6976E68C32ABE (aircraft_compatibility_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE manufacturer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX unique_manufacturer_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE aircraft ADD CONSTRAINT FK_13D96729A23B42D FOREIGN KEY (manufacturer_id) REFERENCES manufacturer (id)');
        $this->addSql('ALTER TABLE aircraft_compatibility_product ADD CONSTRAINT FK_5BFA582D68C32ABE FOREIGN KEY (aircraft_compatibility_id) REFERENCES aircraft_compatibility (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE aircraft_compatibility_product ADD CONSTRAINT FK_5BFA582D4584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE aircraft_compatibility_aircraft ADD CONSTRAINT FK_FEE9545568C32ABE FOREIGN KEY (aircraft_compatibility_id) REFERENCES aircraft_compatibility (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE aircraft_compatibility_aircraft ADD CONSTRAINT FK_FEE95455846E2F5C FOREIGN KEY (aircraft_id) REFERENCES aircraft (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE aircraft_compatibility_files ADD CONSTRAINT FK_BDF6976E68C32ABE FOREIGN KEY (aircraft_compatibility_id) REFERENCES aircraft_compatibility (id)');
        $this->addSql('ALTER TABLE aircraft_compatibility_files ADD CONSTRAINT FK_BDF6976EBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');

        $this->insertFeatureGroup('FEATURE_CREATE_AIRCRAFT', ['ROLE_PSM', 'ROLE_PSE', 'ROLE_COO']);
        $this->insertFeatureGroup('FEATURE_EDIT_AIRCRAFT', ['ROLE_PSM', 'ROLE_PSE', 'ROLE_COO']);
        $this->insertFeatureGroup('FEATURE_DELETE_AIRCRAFT', ['ROLE_PSM', 'ROLE_PSE', 'ROLE_COO']);
        $this->insertFeatureGroup('FEATURE_CREATE_AIRCRAFT_COMPATIBILITY', ['ROLE_PSM', 'ROLE_PSE', 'ROLE_COO']);
        $this->insertFeatureGroup('FEATURE_EDIT_AIRCRAFT_COMPATIBILITY', ['ROLE_PSM', 'ROLE_PSE', 'ROLE_COO']);
        $this->insertFeatureGroup('FEATURE_DELETE_AIRCRAFT_COMPATIBILITY', ['ROLE_PSM', 'ROLE_PSE', 'ROLE_COO']);

        $this->addSql("INSERT INTO manufacturer (name) VALUES ('AIRBUS');");
        $this->addSql("INSERT INTO manufacturer (name) VALUES ('BOEING');");
        $this->addSql("INSERT INTO manufacturer (name) VALUES ('EMBRAER');");
        $this->addSql("INSERT INTO manufacturer (name) VALUES ('DASSAULT AVIATION');");
        $this->addSql("INSERT INTO manufacturer (name) VALUES ('BOMBARDIER');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aircraft DROP FOREIGN KEY FK_13D96729A23B42D');
        $this->addSql('ALTER TABLE aircraft_compatibility_product DROP FOREIGN KEY FK_5BFA582D68C32ABE');
        $this->addSql('ALTER TABLE aircraft_compatibility_product DROP FOREIGN KEY FK_5BFA582D4584665A');
        $this->addSql('ALTER TABLE aircraft_compatibility_aircraft DROP FOREIGN KEY FK_FEE9545568C32ABE');
        $this->addSql('ALTER TABLE aircraft_compatibility_aircraft DROP FOREIGN KEY FK_FEE95455846E2F5C');
        $this->addSql('ALTER TABLE aircraft_compatibility_files DROP FOREIGN KEY FK_BDF6976E68C32ABE');
        $this->addSql('ALTER TABLE aircraft_compatibility_files DROP FOREIGN KEY FK_BDF6976EBF396750');
        $this->addSql('DROP TABLE aircraft');
        $this->addSql('DROP TABLE aircraft_compatibility');
        $this->addSql('DROP TABLE aircraft_compatibility_product');
        $this->addSql('DROP TABLE aircraft_compatibility_aircraft');
        $this->addSql('DROP TABLE aircraft_compatibility_files');
        $this->addSql('DROP TABLE manufacturer');
    }
}
