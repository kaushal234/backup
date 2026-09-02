<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241114134203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create table and feature for LN Quote';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quote (id INT AUTO_INCREMENT NOT NULL, quote_number VARCHAR(255) NOT NULL, xml LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_6B71CBF4AC28B117 (quote_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SALES_QUOTE_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_authorized_application (feature_id, authorized_application_id)
                   SELECT feature.id, authorized_application.id
                     FROM feature, authorized_application
                    WHERE feature.name = "FEATURE_SALES_QUOTE_WRITE"
                      AND authorized_application.name = "ION"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE quote');
    }
}
