<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240416073509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE non_conformity DROP FOREIGN KEY FK_9726A49ACDA5589B');
        $this->addSql('DROP INDEX IDX_9726A49ACDA5589B ON non_conformity');
        $this->addSql('ALTER TABLE non_conformity DROP crab_id, DROP crab_linked');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE non_conformity ADD crab_id INT DEFAULT NULL, ADD crab_linked INT DEFAULT NULL');
        $this->addSql('ALTER TABLE non_conformity ADD CONSTRAINT FK_9726A49ACDA5589B FOREIGN KEY (crab_id) REFERENCES crab (id)');
        $this->addSql('CREATE INDEX IDX_9726A49ACDA5589B ON non_conformity (crab_id)');
    }
}
