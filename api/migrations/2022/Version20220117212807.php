<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220117212807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add portal property to DMS';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dms ADD portal VARCHAR(10) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dms DROP portal');
    }
}
