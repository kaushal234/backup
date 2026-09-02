<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190520074006 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE market_intelligence_files (id INT NOT NULL COMMENT \'(DC2Type:integer)\', market_intelligence_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_22D718E6D0DF19F7 (market_intelligence_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_intelligence (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', legacy_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', poster_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', confidential TINYINT(1) NOT NULL, type VARCHAR(50) NOT NULL COMMENT \'(DC2Type:string)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', short_description VARCHAR(75) NOT NULL COMMENT \'(DC2Type:string)\', description LONGTEXT NOT NULL, INDEX IDX_5DA9408D5BB66C05 (poster_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_intelligence_customer (market_intelligence_id INT NOT NULL COMMENT \'(DC2Type:integer)\', customer_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_735D6308D0DF19F7 (market_intelligence_id), INDEX IDX_735D63089395C3F3 (customer_id), PRIMARY KEY(market_intelligence_id, customer_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_intelligence_competitor (market_intelligence_id INT NOT NULL COMMENT \'(DC2Type:integer)\', competitor_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_2775664AD0DF19F7 (market_intelligence_id), INDEX IDX_2775664A78A5D405 (competitor_id), PRIMARY KEY(market_intelligence_id, competitor_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_intelligence_product_type (market_intelligence_id INT NOT NULL COMMENT \'(DC2Type:integer)\', product_type_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_4A926C8DD0DF19F7 (market_intelligence_id), INDEX IDX_4A926C8D14959723 (product_type_id), PRIMARY KEY(market_intelligence_id, product_type_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_intelligence_dependencies (market_intelligence_id INT NOT NULL COMMENT \'(DC2Type:integer)\', market_intelligence_linked_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_A1AB6988D0DF19F7 (market_intelligence_id), INDEX IDX_A1AB69882123C59F (market_intelligence_linked_id), PRIMARY KEY(market_intelligence_id, market_intelligence_linked_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_intelligence_files ADD CONSTRAINT FK_22D718E6D0DF19F7 FOREIGN KEY (market_intelligence_id) REFERENCES market_intelligence (id)');
        $this->addSql('ALTER TABLE market_intelligence_files ADD CONSTRAINT FK_22D718E6BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_intelligence ADD CONSTRAINT FK_5DA9408D5BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE market_intelligence_customer ADD CONSTRAINT FK_735D6308D0DF19F7 FOREIGN KEY (market_intelligence_id) REFERENCES market_intelligence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_intelligence_customer ADD CONSTRAINT FK_735D63089395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_intelligence_competitor ADD CONSTRAINT FK_2775664AD0DF19F7 FOREIGN KEY (market_intelligence_id) REFERENCES market_intelligence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_intelligence_competitor ADD CONSTRAINT FK_2775664A78A5D405 FOREIGN KEY (competitor_id) REFERENCES competitors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_intelligence_product_type ADD CONSTRAINT FK_4A926C8DD0DF19F7 FOREIGN KEY (market_intelligence_id) REFERENCES market_intelligence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_intelligence_product_type ADD CONSTRAINT FK_4A926C8D14959723 FOREIGN KEY (product_type_id) REFERENCES product_types (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_intelligence_dependencies ADD CONSTRAINT FK_A1AB6988D0DF19F7 FOREIGN KEY (market_intelligence_id) REFERENCES market_intelligence (id)');
        $this->addSql('ALTER TABLE market_intelligence_dependencies ADD CONSTRAINT FK_A1AB69882123C59F FOREIGN KEY (market_intelligence_linked_id) REFERENCES market_intelligence (id)');
        $this->addSql('CREATE INDEX IDX_5DA9408D8CDE5729 ON market_intelligence (type)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MARKET_INTELLIGENCE_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MARKET_INTELLIGENCE_VIEW_CONFIDENTIALS")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_MARKET_INTELLIGENCE_ADMIN"
                          AND user_group.name IN ("SUPERUSER", "GG_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_MARKET_INTELLIGENCE_VIEW_CONFIDENTIALS"
                          AND user_group.name IN ("SUPERUSER", "GG_ADMIN", "GG_EXCOM")');

        $this->addSql('CREATE TABLE market_intelligence_subscription (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', legacy_id INT DEFAULT NULL, competitor_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', customer_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', product_type_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', subscriber_id INT NOT NULL COMMENT \'(DC2Type:integer)\', mim_type VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', INDEX IDX_E8627DD678A5D405 (competitor_id), INDEX IDX_E8627DD69395C3F3 (customer_id), INDEX IDX_E8627DD614959723 (product_type_id), INDEX IDX_E8627DD67808B1AD (subscriber_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_intelligence_subscription ADD CONSTRAINT FK_E8627DD678A5D405 FOREIGN KEY (competitor_id) REFERENCES competitors (id)');
        $this->addSql('ALTER TABLE market_intelligence_subscription ADD CONSTRAINT FK_E8627DD69395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE market_intelligence_subscription ADD CONSTRAINT FK_E8627DD614959723 FOREIGN KEY (product_type_id) REFERENCES product_types (id)');
        $this->addSql('ALTER TABLE market_intelligence_subscription ADD CONSTRAINT FK_E8627DD67808B1AD FOREIGN KEY (subscriber_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE market_intelligence_files DROP FOREIGN KEY FK_22D718E6D0DF19F7');
        $this->addSql('ALTER TABLE market_intelligence_customer DROP FOREIGN KEY FK_735D6308D0DF19F7');
        $this->addSql('ALTER TABLE market_intelligence_competitor DROP FOREIGN KEY FK_2775664AD0DF19F7');
        $this->addSql('ALTER TABLE market_intelligence_product_type DROP FOREIGN KEY FK_4A926C8DD0DF19F7');
        $this->addSql('ALTER TABLE market_intelligence_dependencies DROP FOREIGN KEY FK_A1AB6988D0DF19F7');
        $this->addSql('ALTER TABLE market_intelligence_dependencies DROP FOREIGN KEY FK_A1AB69882123C59F');
        $this->addSql('DROP INDEX IDX_5DA9408D8CDE5729 ON market_intelligence');
        $this->addSql('DROP TABLE market_intelligence_files');
        $this->addSql('DROP TABLE market_intelligence');
        $this->addSql('DROP TABLE market_intelligence_customer');
        $this->addSql('DROP TABLE market_intelligence_competitor');
        $this->addSql('DROP TABLE market_intelligence_product_type');
        $this->addSql('DROP TABLE market_intelligence_dependencies');
        $this->addSql('DROP TABLE market_intelligence_subscription');
    }
}
