<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151116080745 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE acl (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, group_id INT DEFAULT NULL, business_unit_id INT DEFAULT NULL, legacy_id INT DEFAULT NULL, INDEX IDX_BC806D12A76ED395 (user_id), INDEX IDX_BC806D12FE54D947 (group_id), INDEX IDX_BC806D12A58ECB40 (business_unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_group (id INT AUTO_INCREMENT NOT NULL, legacy_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE business_unit (id INT AUTO_INCREMENT NOT NULL, legacy_id INT DEFAULT NULL, name VARCHAR(60) NOT NULL, location VARCHAR(60) NOT NULL, erp VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, business_unit_id INT DEFAULT NULL, position_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, firstname VARCHAR(50) NOT NULL, lastname VARCHAR(50) NOT NULL, nickname VARCHAR(50) DEFAULT NULL, job_title VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, salt VARCHAR(255) NOT NULL, hidden TINYINT(1) NOT NULL, disabled TINYINT(1) NOT NULL, phone VARCHAR(25) DEFAULT NULL, direct_phone VARCHAR(25) NOT NULL, home_phone VARCHAR(25) DEFAULT NULL, mobile VARCHAR(25) DEFAULT NULL, fax VARCHAR(25) DEFAULT NULL, photo VARCHAR(100) DEFAULT NULL, address LONGTEXT DEFAULT NULL, counter INT NOT NULL, last_login DATETIME NOT NULL, legacy_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649A58ECB40 (business_unit_id), INDEX IDX_8D93D649DD842E46 (position_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE acl ADD CONSTRAINT FK_BC806D12A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE acl ADD CONSTRAINT FK_BC806D12FE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE acl ADD CONSTRAINT FK_BC806D12A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES business_unit (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES business_unit (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649DD842E46 FOREIGN KEY (position_id) REFERENCES directory_position (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE acl DROP FOREIGN KEY FK_BC806D12FE54D947');
        $this->addSql('ALTER TABLE acl DROP FOREIGN KEY FK_BC806D12A58ECB40');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A58ECB40');
        $this->addSql('ALTER TABLE acl DROP FOREIGN KEY FK_BC806D12A76ED395');
        $this->addSql('DROP TABLE acl');
        $this->addSql('DROP TABLE user_group');
        $this->addSql('DROP TABLE business_unit');
        $this->addSql('DROP TABLE user');
    }
}
