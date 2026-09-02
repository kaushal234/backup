<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251202125526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove business unit from mis project';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mis_projects DROP FOREIGN KEY FK_6F95DFC5A58ECB40');
        $this->addSql('DROP INDEX IDX_6F95DFC5A58ECB40 ON mis_projects');
        $this->addSql('ALTER TABLE mis_projects DROP business_unit_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mis_projects ADD business_unit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mis_projects ADD CONSTRAINT FK_6F95DFC5A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id)');
        $this->addSql('CREATE INDEX IDX_6F95DFC5A58ECB40 ON mis_projects (business_unit_id)');
    }
}
