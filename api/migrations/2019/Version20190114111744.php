<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190114111744 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE IF EXISTS shortage_files');
        $this->addSql('DROP TABLE IF EXISTS shortage_request');
        $this->addSql("DELETE FROM files WHERE discr='shortage_file'");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE shortage_files (id INT NOT NULL COMMENT \'(DC2Type:integer)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE shortage_request (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', poster_id INT NOT NULL COMMENT \'(DC2Type:integer)\', parameters LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:json)\', INDEX IDX_2E67AED05BB66C05 (poster_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE shortage_files ADD CONSTRAINT FK_651889A2BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shortage_request ADD CONSTRAINT FK_2E67AED05BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
    }
}
