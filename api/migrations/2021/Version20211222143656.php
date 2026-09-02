<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211222143656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add column activated_at to Demo';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demos ADD activated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demos DROP activated_at');
    }
}
