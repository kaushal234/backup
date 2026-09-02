<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260129125528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add disabled property on BusinessUnit';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE directory_businessunit ADD disabled TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE directory_businessunit DROP disabled');
    }
}
