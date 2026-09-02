<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221121203619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add property processes and responsibles on NCR';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE non_conformity_process (non_conformity_id INT NOT NULL, process_id INT NOT NULL, INDEX IDX_4D22BB848EA30491 (non_conformity_id), INDEX IDX_4D22BB847EC2F574 (process_id), PRIMARY KEY(non_conformity_id, process_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE non_conformity_responsible (non_conformity_id INT NOT NULL, responsible_id INT NOT NULL, INDEX IDX_E2A068C18EA30491 (non_conformity_id), INDEX IDX_E2A068C1602AD315 (responsible_id), PRIMARY KEY(non_conformity_id, responsible_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE responsible (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE non_conformity_process ADD CONSTRAINT FK_4D22BB848EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE non_conformity_process ADD CONSTRAINT FK_4D22BB847EC2F574 FOREIGN KEY (process_id) REFERENCES process (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE non_conformity_responsible ADD CONSTRAINT FK_E2A068C18EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE non_conformity_responsible ADD CONSTRAINT FK_E2A068C1602AD315 FOREIGN KEY (responsible_id) REFERENCES responsible (id) ON DELETE CASCADE');

        $this->addSql('INSERT INTO responsible (name) VALUES ("TLD")');
        $this->addSql('INSERT INTO responsible (name) VALUES ("Supplier")');
        $this->addSql('INSERT INTO responsible (name) VALUES ("Customer")');
        $this->addSql('INSERT INTO responsible (name) VALUES ("Other")');
    }

    public function down(Schema $schema): void
    {
    }
}
