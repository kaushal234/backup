<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251103144055 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC backlog report done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE technician_on_call_backlog_report (id INT AUTO_INCREMENT NOT NULL, sales_organisation_id INT NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_6BA995A0E8E5F9D1 (sales_organisation_id), UNIQUE INDEX unique_date_location (sales_organisation_id, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technician_on_call_backlog_association (backlog_id INT NOT NULL, technician_on_call_id INT NOT NULL, INDEX IDX_7995E6AFF1F06ABE (backlog_id), INDEX IDX_7995E6AFEC02A7D0 (technician_on_call_id), PRIMARY KEY(backlog_id, technician_on_call_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE technician_on_call_backlog_report ADD CONSTRAINT FK_6BA995A0E8E5F9D1 FOREIGN KEY (sales_organisation_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE technician_on_call_backlog_association ADD CONSTRAINT FK_7995E6AFF1F06ABE FOREIGN KEY (backlog_id) REFERENCES technician_on_call_backlog_report (id)');
        $this->addSql('ALTER TABLE technician_on_call_backlog_association ADD CONSTRAINT FK_7995E6AFEC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call_backlog_report DROP FOREIGN KEY FK_6BA995A0E8E5F9D1');
        $this->addSql('ALTER TABLE technician_on_call_backlog_association DROP FOREIGN KEY FK_7995E6AFF1F06ABE');
        $this->addSql('ALTER TABLE technician_on_call_backlog_association DROP FOREIGN KEY FK_7995E6AFEC02A7D0');
        $this->addSql('DROP TABLE technician_on_call_backlog_report');
        $this->addSql('DROP TABLE technician_on_call_backlog_association');
    }
}
