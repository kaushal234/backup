<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170609055758 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE directory_location DROP FOREIGN KEY FK_5A26BC26A58ECB40');
        $this->addSql('DROP INDEX IDX_5A26BC26A58ECB40 ON directory_location');
        $this->addSql('ALTER TABLE directory_businessunit ADD location_id INT NOT NULL DEFAULT 1');
        $moveIdsQuery = <<<'SQL'
            UPDATE directory_businessunit, directory_location
            SET directory_businessunit.location_id = directory_location.id
            WHERE directory_location.business_unit_id = directory_businessunit.id
            SQL;
        $this->addSql($moveIdsQuery);
        $this->addSql('ALTER TABLE directory_businessunit CHANGE location_id location_id INT NOT NULL');
        $this->addSql('ALTER TABLE directory_location DROP business_unit_id');
        $this->addSql('ALTER TABLE directory_businessunit ADD CONSTRAINT FK_22677AEE64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_22677AEE64D218E ON directory_businessunit (location_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE directory_businessunit DROP FOREIGN KEY FK_22677AEE64D218E');
        $this->addSql('DROP INDEX UNIQ_22677AEE64D218E ON directory_businessunit');
        $this->addSql('ALTER TABLE directory_location ADD business_unit_id INT DEFAULT NULL');
        $moveIdsQuery = <<<'SQL'
            UPDATE directory_location, directory_businessunit
            SET directory_location.business_unit_id = directory_businessunit.id
            WHERE directory_location.id = directory_businessunit.location_id
            SQL;
        $this->addSql($moveIdsQuery);
        $this->addSql('ALTER TABLE directory_businessunit DROP location_id');
        $this->addSql('ALTER TABLE directory_location ADD CONSTRAINT FK_5A26BC26A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id)');
        $this->addSql('CREATE INDEX IDX_5A26BC26A58ECB40 ON directory_location (business_unit_id)');
    }
}
