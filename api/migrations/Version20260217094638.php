<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260217094638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add status on modules';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE modules ADD status VARCHAR(255) NOT NULL, DROP disabledForTroubleTicket');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE modules ADD disabledForTroubleTicket TINYINT(1) NOT NULL, DROP status');
    }
}
