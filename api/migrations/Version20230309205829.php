<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230309205829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add property safety on NCR and Add the "others" choice for responsible';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE non_conformity ADD safety TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('INSERT IGNORE INTO process (category, description) VALUES ("Others", "-")');
    }

    public function down(Schema $schema): void
    {
        // nothing
    }
}
