<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200929113047 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE invoice_records ADD revised_due_date DATETIME DEFAULT NULL, ADD last_commented_at DATETIME DEFAULT NULL, DROP calculated_due_date, CHANGE category category VARCHAR(255) DEFAULT NULL, CHANGE expected_payment_date expected_payment_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE account_receivables ADD delinquent TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account_receivables DROP delinquent');
        $this->addSql('ALTER TABLE invoice_records ADD calculated_due_date DATETIME DEFAULT \'NULL\', DROP revised_due_date, DROP last_commented_at, CHANGE category category VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE expected_payment_date expected_payment_date DATETIME NOT NULL');
    }
}
