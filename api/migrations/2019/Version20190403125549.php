<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190403125549 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE exchange_rates (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', currency_id INT NOT NULL COMMENT \'(DC2Type:integer)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', applicated_on DATE NOT NULL, type VARCHAR(3) NOT NULL COMMENT \'(DC2Type:string)\', rate NUMERIC(12, 8) NOT NULL, legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_5AE3E77438248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE exchange_rates ADD CONSTRAINT FK_5AE3E77438248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');

        $this->addSql('INSERT IGNORE INTO feature (name) 	VALUES("FEATURE_EXCHANGE_RATE_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_EXCHANGE_RATE_WRITE"
			AND user_group.name in ( "ERP_FOREX", "SUPERUSER" )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE exchange_rates');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_EXCHANGE_RATE_WRITE")');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_EXCHANGE_RATE_WRITE"');
    }
}
