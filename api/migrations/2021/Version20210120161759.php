<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210120161759 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE meeting_actions CHANGE description description LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE meeting_actions CHANGE description description VARCHAR(64) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
