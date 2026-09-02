<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161004083525 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE countries (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, alternate_names LONGTEXT NOT NULL, iso_code_2 VARCHAR(2) NOT NULL, iso_code_3 VARCHAR(3) NOT NULL, nb_code INT NOT NULL, fips_code VARCHAR(10) DEFAULT NULL, fips_name VARCHAR(100) DEFAULT NULL, region VARCHAR(100) NOT NULL, sub_region VARCHAR(100) NOT NULL, latitude VARCHAR(20) DEFAULT NULL, longitude VARCHAR(20) DEFAULT NULL, legacy_id INT NOT NULL, UNIQUE INDEX UNIQ_5D66EBAD5E237E06 (name), UNIQUE INDEX UNIQ_5D66EBAD41AE7779 (iso_code_2), UNIQUE INDEX UNIQ_5D66EBAD36A947EF (iso_code_3), UNIQUE INDEX UNIQ_5D66EBADEFCBF109 (nb_code), INDEX IDX_5D66EBAD5E237E0641AE7779 (name, iso_code_2), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_COUNTRY_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_COUNTRY_WRITE"
                          AND user_group.name = "SUPERUSER"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE countries');
    }
}
