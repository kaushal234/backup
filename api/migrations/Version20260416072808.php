<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416072808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update last emission rating to correct legacy id.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE emission_ratings SET legacy_id=4478 WHERE legacy_id=4477');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE emission_ratings SET legacy_id=4477 WHERE legacy_id=4478');
    }
}
