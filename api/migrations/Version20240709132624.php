<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240709132624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add banner text on news';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE news ADD banner_text LONGTEXT DEFAULT NULL, ADD major_incident TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE news DROP banner_text, DROP major_incident');
    }
}
