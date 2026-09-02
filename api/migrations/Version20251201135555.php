<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251201135555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add region property to MIS project';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mis_projects ADD region_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mis_projects ADD CONSTRAINT FK_6F95DFC598260155 FOREIGN KEY (region_id) REFERENCES directory_region (id)');
        $this->addSql('CREATE INDEX IDX_6F95DFC598260155 ON mis_projects (region_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mis_projects DROP FOREIGN KEY FK_6F95DFC598260155');
        $this->addSql('DROP INDEX IDX_6F95DFC598260155 ON mis_projects');
        $this->addSql('ALTER TABLE mis_projects DROP region_id');
    }
}
