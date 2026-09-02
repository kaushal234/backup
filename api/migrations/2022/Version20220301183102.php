<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220301183102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add specific feature for evendors authorized app to read comments';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_COMMENT_READ")');
    }

    public function down(Schema $schema): void
    {
    }
}
