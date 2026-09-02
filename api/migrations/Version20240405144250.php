<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240405144250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set application for already existing modules';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE modules SET application_id = 6 WHERE application_id IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
