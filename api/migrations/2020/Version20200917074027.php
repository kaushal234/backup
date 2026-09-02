<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200917074027 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE customers DROP FOREIGN KEY FK_62534E21C54C8C93');
        $this->addSql('DROP INDEX IDX_62534E21C54C8C93 ON customers');
        $this->addSql('ALTER TABLE customers DROP type_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE customers ADD type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customers ADD CONSTRAINT FK_62534E21C54C8C93 FOREIGN KEY (type_id) REFERENCES customer_types (id)');
        $this->addSql('CREATE INDEX IDX_62534E21C54C8C93 ON customers (type_id)');
    }
}
