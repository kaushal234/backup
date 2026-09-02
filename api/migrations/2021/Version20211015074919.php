<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211015074919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add new property "linkAlloacted" to Demo entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demos ADD link_allocated TINYINT(1) NOT NULL');
        $this->addSql('UPDATE demos SET link_allocated = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demos DROP link_allocated');
    }
}
