<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150915061624 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE acronym (id INT AUTO_INCREMENT NOT NULL, legacy_id VARCHAR(255) NOT NULL, acronym VARCHAR(10) NOT NULL, description LONGTEXT NOT NULL, short_description VARCHAR(100) NOT NULL, url VARCHAR(120) DEFAULT NULL, UNIQUE INDEX UNIQ_512D8851512D8851 (acronym), INDEX IDX_512D8851512D88519BE5A5B1F47645AE (acronym, short_description, url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acronym_acronym_category (acronym_id INT NOT NULL, acronym_category_id INT NOT NULL, INDEX IDX_21924249BDBACFD0 (acronym_id), INDEX IDX_21924249F98D30F0 (acronym_category_id), PRIMARY KEY(acronym_id, acronym_category_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acronym_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(60) NOT NULL, UNIQUE INDEX UNIQ_A4C314F55E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE acronym_acronym_category ADD CONSTRAINT FK_21924249BDBACFD0 FOREIGN KEY (acronym_id) REFERENCES acronym (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acronym_acronym_category ADD CONSTRAINT FK_21924249F98D30F0 FOREIGN KEY (acronym_category_id) REFERENCES acronym_category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE acronym_acronym_category DROP FOREIGN KEY FK_21924249BDBACFD0');
        $this->addSql('ALTER TABLE acronym_acronym_category DROP FOREIGN KEY FK_21924249F98D30F0');
        $this->addSql('DROP TABLE acronym');
        $this->addSql('DROP TABLE acronym_acronym_category');
        $this->addSql('DROP TABLE acronym_category');
    }
}
