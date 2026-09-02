<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211025094137 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Authorized Application to Log entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity ADD authorized_application_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095AEE16DCEF FOREIGN KEY (authorized_application_id) REFERENCES authorized_application (id)');
        $this->addSql('CREATE INDEX IDX_AC74095AEE16DCEF ON activity (authorized_application_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095AEE16DCEF');
        $this->addSql('DROP INDEX IDX_AC74095AEE16DCEF ON activity');
        $this->addSql('ALTER TABLE activity DROP authorized_application_id');
    }
}
