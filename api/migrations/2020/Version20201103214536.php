<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201103214536 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE directory_region DROP type');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE directory_region ADD type VARCHAR(60) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
