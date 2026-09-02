<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190222091007 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE finance_family_location (finance_family_id INT NOT NULL COMMENT \'(DC2Type:integer)\', location_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_F6C9F38BA0896C46 (finance_family_id), INDEX IDX_F6C9F38B64D218E (location_id), PRIMARY KEY(finance_family_id, location_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE finance_family_location ADD CONSTRAINT FK_F6C9F38BA0896C46 FOREIGN KEY (finance_family_id) REFERENCES finance_families (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE finance_family_location ADD CONSTRAINT FK_F6C9F38B64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE finance_families ADD archived TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE finance_family_location');
        $this->addSql('ALTER TABLE finance_families DROP archived');
    }
}
