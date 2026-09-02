<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230316100758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow adding logo file to location.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE location_files (id INT NOT NULL, location_id INT DEFAULT NULL, INDEX IDX_7069057C64D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE location_files ADD CONSTRAINT FK_7069057C64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE location_files ADD CONSTRAINT FK_7069057CBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location_files DROP FOREIGN KEY FK_7069057C64D218E');
        $this->addSql('ALTER TABLE location_files DROP FOREIGN KEY FK_7069057CBF396750');
        $this->addSql('DROP TABLE location_files');
    }
}
