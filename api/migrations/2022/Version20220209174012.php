<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20220209174012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add specific features for evendors authorized app to edit purchase order confirm date';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PURCHASE_CONFIRM_DATE_WRITE")');
    }

    public function down(Schema $schema): void
    {
    }
}
