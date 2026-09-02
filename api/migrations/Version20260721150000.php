<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_contract flag and contract link on customers_files table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customers_files ADD is_contract TINYINT(1) DEFAULT 0 NOT NULL, ADD contract_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customers_files ADD CONSTRAINT FK_CUSTOMERS_FILES_CONTRACT FOREIGN KEY (contract_id) REFERENCES contract (id)');
        $this->addSql('CREATE INDEX IDX_CUSTOMERS_FILES_CONTRACT ON customers_files (contract_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customers_files DROP FOREIGN KEY FK_CUSTOMERS_FILES_CONTRACT');
        $this->addSql('DROP INDEX IDX_CUSTOMERS_FILES_CONTRACT ON customers_files');
        $this->addSql('ALTER TABLE customers_files DROP is_contract, DROP contract_id');
    }
}
