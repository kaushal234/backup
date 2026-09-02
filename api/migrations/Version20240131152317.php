<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240131152317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add properties on Trouble Ticket';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trouble_ticket ADD url LONGTEXT DEFAULT NULL, ADD referer LONGTEXT DEFAULT NULL, ADD host_name LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trouble_ticket DROP url, DROP referer, DROP host_name');
    }
}
