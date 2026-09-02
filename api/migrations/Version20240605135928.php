<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240605135928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_setting (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, module_id INT DEFAULT NULL, settings JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_C779A692A76ED395 (user_id), INDEX IDX_C779A692AFC2B591 (module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_setting ADD CONSTRAINT FK_C779A692A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_setting ADD CONSTRAINT FK_C779A692AFC2B591 FOREIGN KEY (module_id) REFERENCES modules (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_setting DROP FOREIGN KEY FK_C779A692A76ED395');
        $this->addSql('ALTER TABLE user_setting DROP FOREIGN KEY FK_C779A692AFC2B591');
        $this->addSql('DROP TABLE user_setting');
    }
}
