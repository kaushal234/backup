<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240821133830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add LegalEntity on People';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD legal_entity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6496DEC420C FOREIGN KEY (legal_entity_id) REFERENCES directory_businessunit (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6496DEC420C ON user (legal_entity_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6496DEC420C');
        $this->addSql('DROP INDEX IDX_8D93D6496DEC420C ON user');
        $this->addSql('ALTER TABLE user DROP legal_entity_id');
    }
}
