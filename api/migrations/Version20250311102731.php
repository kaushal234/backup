<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250311102731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add cascade delete on thirdParty Entities properties';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE third_party_app_member DROP FOREIGN KEY FK_9366F33FA76ED395');
        $this->addSql('ALTER TABLE third_party_app_member CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE third_party_app_member ADD CONSTRAINT FK_9366F33FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE third_party_app_update_task DROP FOREIGN KEY FK_862B28A8A76ED395');
        $this->addSql('ALTER TABLE third_party_app_update_task CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE third_party_app_update_task ADD CONSTRAINT FK_862B28A8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE third_party_app_member DROP FOREIGN KEY FK_9366F33FA76ED395');
        $this->addSql('ALTER TABLE third_party_app_member CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE third_party_app_member ADD CONSTRAINT FK_9366F33FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE third_party_app_update_task DROP FOREIGN KEY FK_862B28A8A76ED395');
        $this->addSql('ALTER TABLE third_party_app_update_task CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE third_party_app_update_task ADD CONSTRAINT FK_862B28A8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }
}
