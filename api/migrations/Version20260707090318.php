<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260707090318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add properties to guest user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE guest_user_extended (guest_user_id INT NOT NULL, extended_id INT NOT NULL, INDEX IDX_90367287E7AB17D9 (guest_user_id), INDEX IDX_90367287AEA48C43 (extended_id), PRIMARY KEY(guest_user_id, extended_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE guest_user_extended ADD CONSTRAINT FK_90367287E7AB17D9 FOREIGN KEY (guest_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guest_user_extended ADD CONSTRAINT FK_90367287AEA48C43 FOREIGN KEY (extended_id) REFERENCES modules (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD needs_collaboration_access TINYINT(1) DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guest_user_extended DROP FOREIGN KEY FK_90367287E7AB17D9');
        $this->addSql('ALTER TABLE guest_user_extended DROP FOREIGN KEY FK_90367287AEA48C43');
        $this->addSql('DROP TABLE guest_user_extended');
        $this->addSql('ALTER TABLE user DROP needs_collaboration_access');
    }
}
