<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250106123606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add closest airport property on people';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD closest_airport_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649247EE691 FOREIGN KEY (closest_airport_id) REFERENCES iata_codes (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649247EE691 ON user (closest_airport_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649247EE691');
        $this->addSql('DROP INDEX IDX_8D93D649247EE691 ON user');
        $this->addSql('ALTER TABLE user DROP closest_airport_id');
    }
}
