<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251103150503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update contract entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contract ADD observation_value VARCHAR(255) DEFAULT NULL, ADD observation_term VARCHAR(255) DEFAULT NULL, ADD automatic_renewal TINYINT(1) NOT NULL, DROP signature_date');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contract ADD signature_date DATETIME NOT NULL, DROP observation_value, DROP observation_term, DROP automatic_renewal');
    }
}
