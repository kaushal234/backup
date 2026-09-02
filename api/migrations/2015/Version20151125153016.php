<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151125153016 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE acl DROP FOREIGN KEY FK_BC806D12A58ECB40');
        $this->addSql('ALTER TABLE directory_location DROP FOREIGN KEY FK_5A26BC26A58ECB40');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A58ECB40');

        $this->addSql('ALTER TABLE business_unit RENAME TO directory_businessunit');

        $this->addSql('ALTER TABLE acl ADD CONSTRAINT FK_BC806D12A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id)');
        $this->addSql('ALTER TABLE directory_location ADD CONSTRAINT FK_5A26BC26A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id)');

        $this->addSql('DROP INDEX uniq_8c200e5e5e237e06 ON directory_businessunit');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_22677AEE5E237E06 ON directory_businessunit (name)');

        $this->addSql('ALTER TABLE directory_businessunit DROP FOREIGN KEY FK_8C200E5EFC3FF006');
        $this->addSql('DROP INDEX idx_8c200e5efc3ff006 ON directory_businessunit');
        $this->addSql('CREATE INDEX IDX_22677AEEFC3FF006 ON directory_businessunit (representative_id)');
        $this->addSql('ALTER TABLE directory_businessunit ADD CONSTRAINT FK_8C200E5EFC3FF006 FOREIGN KEY (representative_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE directory_businessunit DROP FOREIGN KEY FK_22677AEEFC3FF006');
        $this->addSql('DROP INDEX idx_22677aeefc3ff006 ON directory_businessunit');
        $this->addSql('CREATE INDEX IDX_8C200E5EFC3FF006 ON directory_businessunit (representative_id)');
        $this->addSql('ALTER TABLE directory_businessunit ADD CONSTRAINT FK_22677AEEFC3FF006 FOREIGN KEY (representative_id) REFERENCES user (id)');

        $this->addSql('ALTER TABLE acl DROP FOREIGN KEY FK_BC806D12A58ECB40');
        $this->addSql('ALTER TABLE directory_location DROP FOREIGN KEY FK_5A26BC26A58ECB40');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A58ECB40');

        $this->addSql('ALTER TABLE directory_businessunit RENAME TO business_unit');

        $this->addSql('ALTER TABLE acl ADD CONSTRAINT FK_BC806D12A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES business_unit (id)');
        $this->addSql('ALTER TABLE directory_location ADD CONSTRAINT FK_5A26BC26A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES business_unit (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES business_unit (id)');

        $this->addSql('DROP INDEX uniq_22677aee5e237e06 ON directory_businessunit');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8C200E5E5E237E06 ON directory_businessunit (name)');
    }
}
