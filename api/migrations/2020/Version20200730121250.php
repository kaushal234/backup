<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200730121250 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE credit_limit (id INT AUTO_INCREMENT NOT NULL, currency_id INT NOT NULL, customer_id INT NOT NULL, type VARCHAR(255) NOT NULL, amount INT NOT NULL, INDEX IDX_27F966CE38248176 (currency_id), INDEX IDX_27F966CE9395C3F3 (customer_id), UNIQUE INDEX unique_credit_limit_type_per_customer (type, customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE credit_limit ADD CONSTRAINT FK_27F966CE38248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('ALTER TABLE credit_limit ADD CONSTRAINT FK_27F966CE9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE credit_limit');
    }
}
