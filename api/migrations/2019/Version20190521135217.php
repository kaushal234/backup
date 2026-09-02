<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190521135217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE directory_location ADD currency_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE directory_location ADD CONSTRAINT FK_5A26BC2638248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('CREATE INDEX IDX_5A26BC2638248176 ON directory_location (currency_id)');
        $this->addSql('UPDATE directory_location dl, currencies c SET dl.currency_id = c.id WHERE c.name = dl.currency');
        $this->addSql('ALTER TABLE directory_location DROP currency');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE directory_location DROP FOREIGN KEY FK_5A26BC2638248176');
        $this->addSql('DROP INDEX IDX_5A26BC2638248176 ON directory_location');
        $this->addSql('ALTER TABLE directory_location ADD currency VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, DROP currency_id');
    }
}
