<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200320134639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"ACU - Blower",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"ACU - Condenser fan",NOW(),"faq")');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM tags WHERE name = "ACU - Blower"');
        $this->addSql('DELETE FROM tags WHERE name = "ACU - Condenser fan"');
    }
}
