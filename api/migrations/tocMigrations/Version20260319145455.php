<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260319145455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC add third party hours and description done';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call ADD third_party_hours INT DEFAULT NULL, ADD third_party_job_description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call DROP third_party_hours, DROP third_party_job_description');
    }
}
