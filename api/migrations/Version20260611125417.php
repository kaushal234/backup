<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260611125417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create part_number_task Table. ';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE part_number_task (part_number VARCHAR(100) NOT NULL, id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE part_number_task ADD CONSTRAINT FK_8C8663BCBF396750 FOREIGN KEY (id) REFERENCES base_task (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE part_number_task DROP FOREIGN KEY FK_8C8663BCBF396750');
        $this->addSql('DROP TABLE part_number_task');
    }
}
