<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221010182145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change Sales Order column on trackings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trackings CHANGE sales_order sales_order VARCHAR(9) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trackings CHANGE sales_order sales_order INT DEFAULT NULL');
    }
}
