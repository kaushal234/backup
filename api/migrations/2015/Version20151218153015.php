<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151218153015 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE acl DROP FOREIGN KEY FK_BC806D12A58ECB40');
        $this->addSql('DROP INDEX IDX_BC806D12A58ECB40 ON acl');
        $this->addSql('ALTER TABLE acl CHANGE business_unit_id location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE acl ADD CONSTRAINT FK_BC806D1264D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('CREATE INDEX IDX_BC806D1264D218E ON acl (location_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE acl DROP FOREIGN KEY FK_BC806D1264D218E');
        $this->addSql('DROP INDEX IDX_BC806D1264D218E ON acl');
        $this->addSql('ALTER TABLE acl CHANGE location_id business_unit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE acl ADD CONSTRAINT FK_BC806D12A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id)');
        $this->addSql('CREATE INDEX IDX_BC806D12A58ECB40 ON acl (business_unit_id)');
    }
}
