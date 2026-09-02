<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds the directory_division_representative join table linking divisions to their representatives (people).
 */
final class Version20260603145212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add directory_division_representative join table (Division <-> People representatives)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE directory_division_representative (division_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_F7AC434541859289 (division_id), INDEX IDX_F7AC43453147C936 (people_id), PRIMARY KEY(division_id, people_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE directory_division_representative ADD CONSTRAINT FK_F7AC434541859289 FOREIGN KEY (division_id) REFERENCES directory_division (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE directory_division_representative ADD CONSTRAINT FK_F7AC43453147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE directory_division_representative DROP FOREIGN KEY FK_F7AC434541859289');
        $this->addSql('ALTER TABLE directory_division_representative DROP FOREIGN KEY FK_F7AC43453147C936');
        $this->addSql('DROP TABLE directory_division_representative');
    }
}
