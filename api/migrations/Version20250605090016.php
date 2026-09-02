<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250605090016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add latitude and longitude fields to Premise entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE premises ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE premises DROP latitude, DROP longitude');
    }
}
