<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170726081940 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE spq_quotation_lines CHANGE quotation_id quotation_id INT NOT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE spq_quotations ADD currency VARCHAR(3) NOT NULL COMMENT \'(DC2Type:string)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE spq_quotation_lines CHANGE quotation_id quotation_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE spq_quotations DROP currency');
    }
}
