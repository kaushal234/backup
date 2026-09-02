<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151118110431 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE directory_juridical_location (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(250) NOT NULL, legacy_id INT NOT NULL, address_street VARCHAR(255) NOT NULL, address_postal_code VARCHAR(7) DEFAULT NULL, address_city VARCHAR(255) NOT NULL, address_state VARCHAR(255) DEFAULT NULL, address_country VARCHAR(2) NOT NULL, UNIQUE INDEX UNIQ_B62ED5B95E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE directory_department CHANGE legacy_id legacy_id INT NOT NULL');
        $this->addSql('DROP INDEX uniq_707eacb0cd1de18a ON directory_department');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_707EACB05E237E06 ON directory_department (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A58ECB40');
        $this->addSql('DROP TABLE directory_juridical_location');
        $this->addSql('ALTER TABLE directory_department CHANGE legacy_id legacy_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX uniq_707eacb05e237e06 ON directory_department');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_707EACB0CD1DE18A ON directory_department (name)');
    }
}
