<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221215080722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove SPOC feature';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_COOKIE_ALVEST_SPOC")');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_COOKIE_ALVEST_SPOC"');
    }

    public function down(Schema $schema): void
    {
    }
}
