<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240425142923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add missing foreign key and index on modules table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE modules ADD CONSTRAINT FK_2EB743D74D3A0D98 FOREIGN KEY (mis_owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2EB743D74D3A0D98 ON modules (mis_owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE modules DROP FOREIGN KEY FK_2EB743D74D3A0D98');
        $this->addSql('DROP INDEX IDX_2EB743D74D3A0D98 ON modules');
    }
}
