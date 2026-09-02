<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170907085644 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customers CHANGE name name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customers CHANGE name name VARCHAR(60) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
    }
}
