<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180703091718 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE demo_people (demo_id INT NOT NULL COMMENT \'(DC2Type:integer)\', people_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_54EA1D5D214B61EA (demo_id), INDEX IDX_54EA1D5D3147C936 (people_id), PRIMARY KEY(demo_id, people_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE demo_people ADD CONSTRAINT FK_54EA1D5D214B61EA FOREIGN KEY (demo_id) REFERENCES demos (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE demo_people ADD CONSTRAINT FK_54EA1D5D3147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE demos ADD expected_closing_status VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE airport_id airport_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE demo_people');
        $this->addSql('ALTER TABLE demos DROP expected_closing_status');
        $this->addSql('ALTER TABLE demos_files CHANGE demo_id demo_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE airport_id airport_id INT NOT NULL COMMENT \'(DC2Type:integer)\'');
    }
}
