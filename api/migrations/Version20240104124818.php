<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240104124818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add market intelligence type entity to make it administrable';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE market_intelligence_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_intelligence ADD type_id INT DEFAULT NULL, CHANGE type type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE market_intelligence ADD CONSTRAINT FK_5DA9408DC54C8C93 FOREIGN KEY (type_id) REFERENCES market_intelligence_type (id)');
        $this->addSql('CREATE INDEX IDX_5DA9408DC54C8C93 ON market_intelligence (type_id)');
        $this->addSql('ALTER TABLE market_intelligence_subscription ADD type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE market_intelligence_subscription ADD CONSTRAINT FK_E8627DD6C54C8C93 FOREIGN KEY (type_id) REFERENCES market_intelligence_type (id)');
        $this->addSql('CREATE INDEX IDX_E8627DD6C54C8C93 ON market_intelligence_subscription (type_id)');

        $this->addSql("INSERT INTO market_intelligence_type (name) VALUES ('Airframer information');");
        $this->addSql("INSERT INTO market_intelligence_type (name) VALUES ('Company financial information');");
        $this->addSql("INSERT INTO market_intelligence_type (name) VALUES ('General documents / newspaper article');");
        $this->addSql("INSERT INTO market_intelligence_type (name) VALUES ('Market rumor');");
        $this->addSql("INSERT INTO market_intelligence_type (name) VALUES ('Miscellaneous Information');");
        $this->addSql("INSERT INTO market_intelligence_type (name) VALUES ('New contract / commercial information');");
        $this->addSql("INSERT INTO market_intelligence_type (name) VALUES ('Pricing info');");
        $this->addSql("INSERT INTO market_intelligence_type (name) VALUES ('Technical datasheet');");
        $this->addSql("INSERT INTO market_intelligence_type (name) VALUES ('Technical document');");
        $this->addSql("INSERT INTO market_intelligence_type (name) VALUES ('Fleet list');");

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MARKET_INTELLIGENCE_TYPE_WRITE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MARKET_INTELLIGENCE_TYPE_WRITE"
          AND user_group.name in ("SUPERUSER")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_intelligence DROP FOREIGN KEY FK_5DA9408DC54C8C93');
        $this->addSql('ALTER TABLE market_intelligence_subscription DROP FOREIGN KEY FK_E8627DD6C54C8C93');
        $this->addSql('DROP TABLE market_intelligence_type');
        $this->addSql('DROP INDEX IDX_5DA9408DC54C8C93 ON market_intelligence');
        $this->addSql('ALTER TABLE market_intelligence DROP type_id, CHANGE type type VARCHAR(50) NOT NULL');
        $this->addSql('DROP INDEX IDX_E8627DD6C54C8C93 ON market_intelligence_subscription');
        $this->addSql('ALTER TABLE market_intelligence_subscription DROP type_id');
    }
}
