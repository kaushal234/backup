<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210209132404 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spq_quotations ADD first_submitted_at DATETIME DEFAULT NULL AFTER submitted_at');
        $this->addSql('UPDATE spq_quotations SET first_submitted_at = submitted_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spq_quotations DROP first_submitted_at');
    }
}
