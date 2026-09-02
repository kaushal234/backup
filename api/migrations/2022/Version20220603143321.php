<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220603143321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix metadata column in activity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE activity CHANGE metadata metadata LONGTEXT DEFAULT \'[]\' NOT NULL');
        $this->addSql('UPDATE activity SET metadata = \'[]\'');
    }

    public function down(Schema $schema): void
    {
    }
}
