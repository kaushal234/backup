<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241004100251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add new feature to allow access on people information';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PEOPLE_READ")');
    }

    public function down(Schema $schema): void
    {
    }
}
