<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180105080721 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customers ALTER COLUMN status SET DEFAULT \'PENDING\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customers ALTER COLUMN status SET DEFAULT \'APPROVED\'');
    }
}
