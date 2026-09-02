<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260528144448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add renew guest user task table.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE renew_guest_user (renewal_decision VARCHAR(10) NOT NULL, renewal_duration_months INT DEFAULT NULL, guest_user_id INT DEFAULT NULL, id INT NOT NULL, INDEX IDX_60693758E7AB17D9 (guest_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE renew_guest_user ADD CONSTRAINT FK_60693758E7AB17D9 FOREIGN KEY (guest_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE renew_guest_user ADD CONSTRAINT FK_60693758BF396750 FOREIGN KEY (id) REFERENCES base_task (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE renew_guest_user DROP FOREIGN KEY FK_60693758E7AB17D9');
        $this->addSql('ALTER TABLE renew_guest_user DROP FOREIGN KEY FK_60693758BF396750');
        $this->addSql('DROP TABLE renew_guest_user');
    }
}
