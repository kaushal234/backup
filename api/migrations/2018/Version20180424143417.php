<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180424143417 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE directory_businessunit CHANGE legacy_id legacy_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE directory_businessunit  CHANGE legacy_id legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\'');
    }
}
