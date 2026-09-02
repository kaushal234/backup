<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230914081521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add closed at and closed by on derogation';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE derogation ADD closed_by_id INT DEFAULT NULL, ADD closed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE derogation ADD CONSTRAINT FK_E46E3F3AE1FA7797 FOREIGN KEY (closed_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_E46E3F3AE1FA7797 ON derogation (closed_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE derogation DROP FOREIGN KEY FK_E46E3F3AE1FA7797');
        $this->addSql('DROP INDEX IDX_E46E3F3AE1FA7797 ON derogation');
        $this->addSql('ALTER TABLE derogation DROP closed_by_id, DROP closed_at');
    }
}
