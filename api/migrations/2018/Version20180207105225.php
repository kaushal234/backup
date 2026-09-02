<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180207105225 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE leasing_contracts ADD end_user_id INT NOT NULL COMMENT \'(DC2Type:integer)\', ADD buyer_id INT NOT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE leasing_contracts ADD CONSTRAINT FK_976D1AE532A1827C FOREIGN KEY (end_user_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE leasing_contracts ADD CONSTRAINT FK_976D1AE56C755722 FOREIGN KEY (buyer_id) REFERENCES customers (id)');
        $this->addSql('CREATE INDEX IDX_976D1AE532A1827C ON leasing_contracts (end_user_id)');
        $this->addSql('CREATE INDEX IDX_976D1AE56C755722 ON leasing_contracts (buyer_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE leasing_contracts DROP FOREIGN KEY FK_976D1AE532A1827C');
        $this->addSql('ALTER TABLE leasing_contracts DROP FOREIGN KEY FK_976D1AE56C755722');
        $this->addSql('DROP INDEX IDX_976D1AE532A1827C ON leasing_contracts');
        $this->addSql('DROP INDEX IDX_976D1AE56C755722 ON leasing_contracts');
        $this->addSql('ALTER TABLE leasing_contracts DROP end_user_id, DROP buyer_id');
    }
}
