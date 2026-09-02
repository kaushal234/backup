<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240911141351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add notification system';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification_templates (id INT AUTO_INCREMENT NOT NULL, module_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, text VARCHAR(255) NOT NULL, INDEX IDX_C9C13AD1AFC2B591 (module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notifications (id INT AUTO_INCREMENT NOT NULL, people_id INT DEFAULT NULL, template_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, reference_id INT NOT NULL, unread TINYINT(1) NOT NULL, text_displayed VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_6000B0D33147C936 (people_id), INDEX IDX_6000B0D35DA0FB8 (template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notification_templates ADD CONSTRAINT FK_C9C13AD1AFC2B591 FOREIGN KEY (module_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D33147C936 FOREIGN KEY (people_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D35DA0FB8 FOREIGN KEY (template_id) REFERENCES notification_templates (id)');
        $this->addSql('ALTER TABLE modules ADD notification_color VARCHAR(255) DEFAULT NULL');

        $this->addSql("INSERT INTO notification_templates (module_id, name, text) VALUES (33, 'trouble_ticket_assigned', 'This TTS is assigned to you and requires action on your side.')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification_templates DROP FOREIGN KEY FK_C9C13AD1AFC2B591');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D33147C936');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D35DA0FB8');
        $this->addSql('DROP TABLE notification_templates');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('ALTER TABLE modules DROP notification_color');
    }
}
