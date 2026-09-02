<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220711203257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add cookie-related features';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE feature SET name="FEATURE_COOKIE_ALVEST_STAGING" WHERE name="FEATURE_STAGING_ACCESS"');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_COOKIE_ALVEST_SPOC")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
