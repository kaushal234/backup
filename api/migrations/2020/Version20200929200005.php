<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200929200005 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE directory_location DROP FOREIGN KEY FK_5A26BC2698260155;');
        $this->addSql('DROP INDEX IDX_5A26BC2698260155 ON directory_location');
        $this->addSql('ALTER TABLE directory_location DROP region_id');

        $this->addSql('DROP TABLE directory_region');

        $this->addSql('RENAME TABLE directory_division TO directory_region');

        $this->addSql('ALTER TABLE directory_division_businessunit DROP FOREIGN KEY FK_4C42657C41859289');
        $this->addSql('DROP INDEX IDX_4C42657C41859289 ON directory_division_businessunit');
        $this->addSql('ALTER TABLE directory_division_businessunit DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE directory_division_businessunit CHANGE division_id region_id INT NOT NULL');
        $this->addSql('ALTER TABLE directory_division_businessunit ADD CONSTRAINT FK_4C42657C98260155 FOREIGN KEY (region_id) REFERENCES directory_region (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4C42657C98260155 ON directory_division_businessunit (region_id)');
        $this->addSql('ALTER TABLE directory_division_businessunit ADD PRIMARY KEY (region_id, business_unit_id)');

        $this->addSql('ALTER TABLE directory_businessunit ADD region_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE directory_businessunit ADD CONSTRAINT FK_22677AEE98260155 FOREIGN KEY (region_id) REFERENCES directory_region (id)');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64941859289;');
        $this->addSql('DROP INDEX IDX_8D93D64941859289 ON user');
        $this->addSql('ALTER TABLE user DROP division_id');
    }

    public function down(Schema $schema): void
    {
        // should not be reverted...
    }
}
