<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180410095033 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE competitors (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', name VARCHAR(50) NOT NULL COMMENT \'(DC2Type:string)\', short_description VARCHAR(100) DEFAULT NULL COMMENT \'(DC2Type:string)\', description LONGTEXT DEFAULT NULL, url VARCHAR(100) DEFAULT NULL COMMENT \'(DC2Type:string)\', legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE competitor_product_type (competitor_id INT NOT NULL COMMENT \'(DC2Type:integer)\', product_type_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_5E77C46778A5D405 (competitor_id), INDEX IDX_5E77C46714959723 (product_type_id), PRIMARY KEY(competitor_id, product_type_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE competitor_product_type ADD CONSTRAINT FK_5E77C46778A5D405 FOREIGN KEY (competitor_id) REFERENCES competitors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE competitor_product_type ADD CONSTRAINT FK_5E77C46714959723 FOREIGN KEY (product_type_id) REFERENCES product_types (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_COMPETITOR_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_COMPETITOR_EDIT"
                          AND user_group.name IN ("SUPERUSER", "ROLE_GTD", "ROLE_GCEO", "ROLE_GCOO")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_COMPETITOR_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_COMPETITOR_CREATE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_GTD", "ROLE_GCEO", "ROLE_GCOO")');

        $this->addSql('CREATE TABLE competitors_files (id INT NOT NULL COMMENT \'(DC2Type:integer)\', competitor_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_672FD2F178A5D405 (competitor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE competitors_files ADD CONSTRAINT FK_672FD2F178A5D405 FOREIGN KEY (competitor_id) REFERENCES competitors (id)');
        $this->addSql('ALTER TABLE competitors_files ADD CONSTRAINT FK_672FD2F1BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE competitor_product_type');

        $this->addSql('DROP TABLE competitors');

        $this->addSql('DROP TABLE competitors_files');
    }
}
