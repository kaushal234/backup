<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170821090608 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spq_quotation_lines CHANGE commodity_code commodity_code VARCHAR(10) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spq_quotation_lines CHANGE commodity_code commodity_code VARCHAR(8) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
    }
}
