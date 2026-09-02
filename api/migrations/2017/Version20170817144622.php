<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170817144622 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // ALTER TABLE tablename AUTO_INCREMENT = 1
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE spq_quotations AUTO_INCREMENT = 10000');
    }

    public function down(Schema $schema): void
    {
    }
}
