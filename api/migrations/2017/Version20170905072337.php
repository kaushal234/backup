<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170905072337 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spq_quotation_lines ADD deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spq_quotation_lines DROP deleted_at');
    }
}
