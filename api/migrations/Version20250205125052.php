<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250205125052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Audit Log table.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE audit_log (id INT AUTO_INCREMENT NOT NULL, next_id INT DEFAULT NULL, created_at DATETIME NOT NULL, property VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, audit_type VARCHAR(255) NOT NULL, reference_id INT NOT NULL, UNIQUE INDEX UNIQ_F6E1C0F5AA23F6C8 (next_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_F6E1C0F5AA23F6C8 FOREIGN KEY (next_id) REFERENCES audit_log (id)');

        $this->addSql('ALTER TABLE audit_log ADD created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_F6E1C0F5B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_F6E1C0F5B03A8386 ON audit_log (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audit_log DROP FOREIGN KEY FK_F6E1C0F5AA23F6C8');
        $this->addSql('ALTER TABLE audit_log DROP FOREIGN KEY FK_F6E1C0F5B03A8386');
        $this->addSql('DROP INDEX IDX_F6E1C0F5B03A8386 ON audit_log');
        $this->addSql('DROP TABLE audit_log');
    }
}
