<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241014080332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add airport on premises';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE premises ADD airport_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE premises ADD CONSTRAINT FK_4A01730A289F53C8 FOREIGN KEY (airport_id) REFERENCES iata_codes (id)');
        $this->addSql('CREATE INDEX IDX_4A01730A289F53C8 ON premises (airport_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE premises DROP FOREIGN KEY FK_4A01730A289F53C8');
        $this->addSql('DROP INDEX IDX_4A01730A289F53C8 ON premises');
        $this->addSql('ALTER TABLE premises DROP airport_id');
    }
}
