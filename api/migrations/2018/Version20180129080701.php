<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180129080701 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_follow_up_reports ADD hourmeter_totalizer INT NOT NULL COMMENT \'(DC2Type:integer)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_follow_up_reports DROP hourmeter_totalizer');
    }
}
