<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260723120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert messenger_messages to utf8mb4 - the table was left on utf8mb3 (excluded from schema_filter)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE messenger_messages CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE messenger_messages CONVERT TO CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci');
    }
}
