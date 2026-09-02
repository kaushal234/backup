<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221201092235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix feature name for evendors news';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM feature WHERE name="AUTHORIZED_APPLICATION_FEATURE_EVENDORS_NEWS_READ"');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_EVENDORS_NEWS_READ")');
        $this->addSql('INSERT IGNORE INTO feature_authorized_application(feature_id, authorized_application_id)
                           SELECT (
                               SELECT feature.id FROM feature WHERE feature.name = "FEATURE_EVENDORS_NEWS_READ"),
                               (SELECT id FROM authorized_application  WHERE name="evendors"
                           )');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
