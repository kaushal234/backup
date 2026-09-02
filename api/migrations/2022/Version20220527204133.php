<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220527204133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove unknown migration from the production database';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("DELETE FROM migration_versions WHERE version LIKE '%Version202200209174012'");
    }
}
