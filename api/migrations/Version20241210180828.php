<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241210180828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add planning daily limit ressource and truck type and comment on ESRL';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE planning_daily_limit (id INT AUTO_INCREMENT NOT NULL, factory_id INT DEFAULT NULL, days INT NOT NULL, comment VARCHAR(255) DEFAULT NULL, UNIQUE INDEX unique_factory (factory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE planning_daily_limit ADD CONSTRAINT FK_B29C49EDC7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE equipment_shipping_record_line ADD truck_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipment_shipping_record_line ADD comment VARCHAR(255) DEFAULT NULL');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PLANNING_DAILY_LIMIT")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_PLANNING_DAILY_LIMIT"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_PSM",
            "ROLE_PSE",
            "ROLE_PSA"
          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE planning_daily_limit DROP FOREIGN KEY FK_B29C49EDC7AF27D2');
        $this->addSql('DROP TABLE planning_daily_limit');
        $this->addSql('ALTER TABLE equipment_shipping_record_line DROP truck_type');
        $this->addSql('ALTER TABLE equipment_shipping_record_line DROP comment');
    }
}
