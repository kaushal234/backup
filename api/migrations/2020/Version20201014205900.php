<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201014205900 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE directory_division_businessunit');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE directory_division_businessunit (region_id INT NOT NULL, business_unit_id INT NOT NULL, INDEX IDX_4C42657CA58ECB40 (business_unit_id), INDEX IDX_4C42657C98260155 (region_id), PRIMARY KEY(region_id, business_unit_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE directory_division_businessunit ADD CONSTRAINT FK_4C42657C98260155 FOREIGN KEY (region_id) REFERENCES directory_region (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE directory_division_businessunit ADD CONSTRAINT FK_4C42657CA58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id) ON DELETE CASCADE');
    }
}
