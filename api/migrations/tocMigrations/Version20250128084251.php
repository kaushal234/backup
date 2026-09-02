<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250128084251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC main file / files / remove estimated hours done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE technician_on_call_files (id INT NOT NULL, technician_on_call_id INT DEFAULT NULL, INDEX IDX_9DF165E0EC02A7D0 (technician_on_call_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technician_on_call_main_files (id INT NOT NULL, technician_on_call_id INT DEFAULT NULL, INDEX IDX_2B41AB3BEC02A7D0 (technician_on_call_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE technician_on_call_files ADD CONSTRAINT FK_9DF165E0EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('ALTER TABLE technician_on_call_files ADD CONSTRAINT FK_9DF165E0BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_on_call_main_files ADD CONSTRAINT FK_2B41AB3BEC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('ALTER TABLE technician_on_call_main_files ADD CONSTRAINT FK_2B41AB3BBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_on_call DROP estimated_hours');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call_files DROP FOREIGN KEY FK_9DF165E0EC02A7D0');
        $this->addSql('ALTER TABLE technician_on_call_files DROP FOREIGN KEY FK_9DF165E0BF396750');
        $this->addSql('ALTER TABLE technician_on_call_main_files DROP FOREIGN KEY FK_2B41AB3BEC02A7D0');
        $this->addSql('ALTER TABLE technician_on_call_main_files DROP FOREIGN KEY FK_2B41AB3BBF396750');
        $this->addSql('DROP TABLE technician_on_call_files');
        $this->addSql('DROP TABLE technician_on_call_main_files');
        $this->addSql('ALTER TABLE technician_on_call ADD estimated_hours INT NOT NULL');
    }
}
