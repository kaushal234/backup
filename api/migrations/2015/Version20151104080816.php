<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151104080816 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE directory_position_level (id INT AUTO_INCREMENT NOT NULL, `label` VARCHAR(60) NOT NULL, UNIQUE INDEX UNIQ_AFED9CDEA750E8 (`label`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE directory_position (id INT AUTO_INCREMENT NOT NULL, level_id INT DEFAULT NULL, code VARCHAR(10) NOT NULL, description VARCHAR(250) NOT NULL, legacy_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_4294D11877153098 (code), INDEX IDX_4294D1185FB14BA7 (level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE directory_position ADD CONSTRAINT FK_4294D1185FB14BA7 FOREIGN KEY (level_id) REFERENCES directory_position_level (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE directory_position DROP FOREIGN KEY FK_4294D1185FB14BA7');
        $this->addSql('DROP TABLE directory_position_level');
        $this->addSql('DROP TABLE directory_position');
    }
}
