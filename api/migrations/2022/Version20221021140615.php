<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221021140615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add link between user and vendor_user';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD vendor_user_linked_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64938D5B9BF FOREIGN KEY (vendor_user_linked_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8D93D64938D5B9BF ON user (vendor_user_linked_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64938D5B9BF');
        $this->addSql('DROP INDEX IDX_8D93D64938D5B9BF ON user');
        $this->addSql('ALTER TABLE user DROP vendor_user_linked_id');
    }
}
