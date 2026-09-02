<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181217133034 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE finance_families (id INT NOT NULL COMMENT \'(DC2Type:integer)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE finance_families ADD CONSTRAINT FK_4809CAD1BF396750 FOREIGN KEY (id) REFERENCES families (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE products ADD finance_family_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5AA0896C46 FOREIGN KEY (finance_family_id) REFERENCES finance_families (id)');
        $this->addSql('CREATE INDEX IDX_B3BA5A5AA0896C46 ON products (finance_family_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5AA0896C46');
        $this->addSql('DROP TABLE finance_families');
        $this->addSql('ALTER TABLE acl CHANs_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE english_name english_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE french_name french_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE spanish_name spanish_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE portuguese_name portuguese_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE chinese_name chinese_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE japanese_name japanese_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE german_name german_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE russian_name russian_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
        $this->addSql('DROP INDEX IDX_B3BA5A5AA0896C46 ON products');
        $this->addSql('ALTER TABLE products DROP finance_family_id');
    }
}
