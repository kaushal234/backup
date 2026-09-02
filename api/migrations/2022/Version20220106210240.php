<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220106210240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change erp employee ID column in user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE erp_employee_id erp_identifier VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE erp_identifier erp_employee_id VARCHAR(255) DEFAULT NULL');
    }
}
