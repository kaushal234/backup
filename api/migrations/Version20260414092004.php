<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260414092004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new tag PCB for FAQ';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at,discr) VALUES (116,"PCB",NOW(),"faq")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM tags WHERE name = "PCB"');
    }
}
