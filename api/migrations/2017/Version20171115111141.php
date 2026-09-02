<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171115111141 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spq_quotations ADD routing VARCHAR(4) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
        $this->addSql('CREATE INDEX IDX_E396917CA5F8B9FA ON spq_quotations (routing)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_E396917CA5F8B9FA ON spq_quotations');
        $this->addSql('ALTER TABLE spq_quotations DROP routing');
    }
}
