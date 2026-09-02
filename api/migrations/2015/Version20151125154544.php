<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151125154544 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE directory_division (id INT AUTO_INCREMENT NOT NULL, representative_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(60) NOT NULL, legacy_id INT NOT NULL, UNIQUE INDEX UNIQ_14AF72F95E237E06 (name), INDEX IDX_14AF72F9FC3FF006 (representative_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE directory_division_businessunit (division_id INT NOT NULL, business_unit_id INT NOT NULL, INDEX IDX_4C42657C41859289 (division_id), INDEX IDX_4C42657CA58ECB40 (business_unit_id), PRIMARY KEY(division_id, business_unit_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE directory_division ADD CONSTRAINT FK_14AF72F9FC3FF006 FOREIGN KEY (representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE directory_division_businessunit ADD CONSTRAINT FK_4C42657C41859289 FOREIGN KEY (division_id) REFERENCES directory_division (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE directory_division_businessunit ADD CONSTRAINT FK_4C42657CA58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE directory_division_businessunit DROP FOREIGN KEY FK_4C42657C41859289');
        $this->addSql('DROP TABLE directory_division');
        $this->addSql('DROP TABLE directory_division_businessunit');
    }
}
