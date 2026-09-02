<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206151602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Guest User entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE directory_position CHANGE code code VARCHAR(12) NOT NULL');
        $this->addSql('ALTER TABLE third_party_app_whitelist DROP FOREIGN KEY FK_6CA75A873147C936');
        $this->addSql('DROP INDEX IDX_6CA75A873147C936 ON third_party_app_whitelist');
        $this->addSql('DROP INDEX `primary` ON third_party_app_whitelist');
        $this->addSql('ALTER TABLE third_party_app_whitelist CHANGE people_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE third_party_app_whitelist ADD CONSTRAINT FK_6CA75A87A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6CA75A87A76ED395 ON third_party_app_whitelist (user_id)');
        $this->addSql('ALTER TABLE third_party_app_whitelist ADD PRIMARY KEY (extended_id, user_id)');
        $this->addSql('ALTER TABLE third_party_app_blacklist DROP FOREIGN KEY FK_9CB691663147C936');
        $this->addSql('DROP INDEX IDX_9CB691663147C936 ON third_party_app_blacklist');
        $this->addSql('DROP INDEX `primary` ON third_party_app_blacklist');
        $this->addSql('ALTER TABLE third_party_app_blacklist CHANGE people_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE third_party_app_blacklist ADD CONSTRAINT FK_9CB69166A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9CB69166A76ED395 ON third_party_app_blacklist (user_id)');
        $this->addSql('ALTER TABLE third_party_app_blacklist ADD PRIMARY KEY (extended_id, user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE directory_position CHANGE code code VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE third_party_app_blacklist DROP FOREIGN KEY FK_9CB69166A76ED395');
        $this->addSql('DROP INDEX IDX_9CB69166A76ED395 ON third_party_app_blacklist');
        $this->addSql('DROP INDEX `PRIMARY` ON third_party_app_blacklist');
        $this->addSql('ALTER TABLE third_party_app_blacklist CHANGE user_id people_id INT NOT NULL');
        $this->addSql('ALTER TABLE third_party_app_blacklist ADD CONSTRAINT FK_9CB691663147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9CB691663147C936 ON third_party_app_blacklist (people_id)');
        $this->addSql('ALTER TABLE third_party_app_blacklist ADD PRIMARY KEY (extended_id, people_id)');
        $this->addSql('ALTER TABLE third_party_app_whitelist DROP FOREIGN KEY FK_6CA75A87A76ED395');
        $this->addSql('DROP INDEX IDX_6CA75A87A76ED395 ON third_party_app_whitelist');
        $this->addSql('DROP INDEX `PRIMARY` ON third_party_app_whitelist');
        $this->addSql('ALTER TABLE third_party_app_whitelist CHANGE user_id people_id INT NOT NULL');
        $this->addSql('ALTER TABLE third_party_app_whitelist ADD CONSTRAINT FK_6CA75A873147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6CA75A873147C936 ON third_party_app_whitelist (people_id)');
        $this->addSql('ALTER TABLE third_party_app_whitelist ADD PRIMARY KEY (extended_id, people_id)');
    }
}
