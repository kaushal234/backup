<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180221110248 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE families (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', discr VARCHAR(20) NOT NULL COMMENT \'(DC2Type:string)\', UNIQUE INDEX unique_name_per_family (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_families (id INT NOT NULL COMMENT \'(DC2Type:integer)\', product_type_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', french_description TEXT DEFAULT NULL, english_description TEXT DEFAULT NULL, spanish_description TEXT DEFAULT NULL, portuguese_description TEXT DEFAULT NULL, chinese_description TEXT DEFAULT NULL, japanese_description TEXT DEFAULT NULL, german_description TEXT DEFAULT NULL, russian_description TEXT DEFAULT NULL, hidden TINYINT(1) NOT NULL, public TINYINT(1) NOT NULL, legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_52EF555B14959723 (product_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', family_id INT NOT NULL COMMENT \'(DC2Type:integer)\', erp_location_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', hidden TINYINT(1) NOT NULL, legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_B3BA5A5AC35E566A (family_id), INDEX IDX_B3BA5A5A34BAA6FC (erp_location_id), UNIQUE INDEX unique_name_per_product (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_family_dms (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', family_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', dms_id INT NOT NULL COMMENT \'(DC2Type:integer)\', dms_type VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_FCD76E2AC35E566A (family_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_types (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', english_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', french_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', spanish_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', portuguese_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', chinese_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', japanese_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', german_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', russian_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', dms_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', UNIQUE INDEX UNIQ_F86CF26C734D08E1 (english_name), UNIQUE INDEX UNIQ_F86CF26C6912EC1F (french_name), UNIQUE INDEX UNIQ_F86CF26C4F00F7F1 (spanish_name), UNIQUE INDEX UNIQ_F86CF26C28CEF572 (portuguese_name), UNIQUE INDEX UNIQ_F86CF26CAFCB79E6 (chinese_name), UNIQUE INDEX UNIQ_F86CF26C41FBE96 (japanese_name), UNIQUE INDEX UNIQ_F86CF26C6A067321 (german_name), UNIQUE INDEX UNIQ_F86CF26CFD3E8DD5 (russian_name), UNIQUE INDEX UNIQ_F86CF26CA38F4C43 (dms_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_families ADD CONSTRAINT FK_52EF555B14959723 FOREIGN KEY (product_type_id) REFERENCES product_types (id)');
        $this->addSql('ALTER TABLE product_families ADD CONSTRAINT FK_52EF555BBF396750 FOREIGN KEY (id) REFERENCES families (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5AC35E566A FOREIGN KEY (family_id) REFERENCES product_families (id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A34BAA6FC FOREIGN KEY (erp_location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE product_family_dms ADD CONSTRAINT FK_FCD76E2AC35E566A FOREIGN KEY (family_id) REFERENCES product_families (id)');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_CATALOG_EDIT"
          AND user_group.name in (
            "SUPERUSER",
            "GG_ADMIN",
            "ROLE_PSM",
            "ROLE_PSE",
            "ROLE_PSA"
          )'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_CATALOG_CREATE"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_PSM"
          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_families DROP FOREIGN KEY FK_52EF555BBF396750');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5AC35E566A');
        $this->addSql('ALTER TABLE product_family_dms DROP FOREIGN KEY FK_FCD76E2AC35E566A');
        $this->addSql('ALTER TABLE product_families DROP FOREIGN KEY FK_52EF555B14959723');
        $this->addSql('DROP TABLE families');
        $this->addSql('DROP TABLE product_families');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE product_family_dms');
        $this->addSql('DROP TABLE product_types');
    }
}
