<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180518143534 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE demos (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', sso_id INT NOT NULL COMMENT \'(DC2Type:integer)\', factory_id INT NOT NULL COMMENT \'(DC2Type:integer)\', asm_id INT NOT NULL COMMENT \'(DC2Type:integer)\', psm_id INT NOT NULL COMMENT \'(DC2Type:integer)\', csm_id INT NOT NULL COMMENT \'(DC2Type:integer)\', ast_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', product_id INT NOT NULL COMMENT \'(DC2Type:integer)\', customer_id INT NOT NULL COMMENT \'(DC2Type:integer)\', country_id INT NOT NULL COMMENT \'(DC2Type:integer)\', airport_id INT NOT NULL COMMENT \'(DC2Type:integer)\', equipment_record_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', future_demo_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', start_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', end_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', closing_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', closing_comment VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', status VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', delinquant TINYINT(1) NOT NULL, INDEX IDX_CD0E2E3C7843BFA4 (sso_id), INDEX IDX_CD0E2E3CC7AF27D2 (factory_id), INDEX IDX_CD0E2E3C9C54D4BF (asm_id), INDEX IDX_CD0E2E3C54DE0581 (psm_id), INDEX IDX_CD0E2E3CD19C75B4 (csm_id), INDEX IDX_CD0E2E3CB145CCAA (ast_id), INDEX IDX_CD0E2E3C4584665A (product_id), INDEX IDX_CD0E2E3C9395C3F3 (customer_id), INDEX IDX_CD0E2E3CF92F3E70 (country_id), INDEX IDX_CD0E2E3C289F53C8 (airport_id), INDEX IDX_CD0E2E3C9FC03375 (equipment_record_id), UNIQUE INDEX UNIQ_CD0E2E3C7E2FF845 (future_demo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE demos_files (id INT NOT NULL COMMENT \'(DC2Type:integer)\', demo_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_F8EC8856214B61EA (demo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3C7843BFA4 FOREIGN KEY (sso_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3CC7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3C9C54D4BF FOREIGN KEY (asm_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3C54DE0581 FOREIGN KEY (psm_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3CD19C75B4 FOREIGN KEY (csm_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3CB145CCAA FOREIGN KEY (ast_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3C4584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3C9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3CF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3C289F53C8 FOREIGN KEY (airport_id) REFERENCES iata_codes (id)');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3C9FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3C7E2FF845 FOREIGN KEY (future_demo_id) REFERENCES demos (id)');
        $this->addSql('ALTER TABLE demos_files ADD CONSTRAINT FK_F8EC8856214B61EA FOREIGN KEY (demo_id) REFERENCES demos (id)');
        $this->addSql('ALTER TABLE demos_files ADD CONSTRAINT FK_F8EC8856BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE demos DROP FOREIGN KEY FK_CD0E2E3C7E2FF845');
        $this->addSql('ALTER TABLE demos_files DROP FOREIGN KEY FK_F8EC8856214B61EA');
        $this->addSql('DROP TABLE demos');
        $this->addSql('DROP TABLE demos_files');
    }
}
