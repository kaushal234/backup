<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220608192844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix once and for all the metadata column using the doctrine default value option in the ORM config';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE activity CHANGE metadata metadata LONGTEXT DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
    }
}
