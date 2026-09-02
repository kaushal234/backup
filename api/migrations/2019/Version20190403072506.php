<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190403072506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Currencies table migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE currencies (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', name VARCHAR(3) NOT NULL COMMENT \'(DC2Type:string)\', legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', UNIQUE INDEX UNIQ_37C446935E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES ("FEATURE_CURRENCY_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                         WHERE feature.name = "FEATURE_CURRENCY_WRITE" AND user_group.name = "SUPERUSER"');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE currencies');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_CURRENCY_WRITE")');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_CURRENCY_WRITE"');
    }
}
