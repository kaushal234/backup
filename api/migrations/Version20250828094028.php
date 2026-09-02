<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828094028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Supplier entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_WRITE_SUPPLIER")');

        $this->addSql('CREATE TABLE suppliers (id INT AUTO_INCREMENT NOT NULL, location_id INT DEFAULT NULL, country_id INT DEFAULT NULL, currency_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_AC28B95C77153098 (code), INDEX IDX_AC28B95C64D218E (location_id), INDEX IDX_AC28B95CF92F3E70 (country_id), INDEX IDX_AC28B95C38248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE suppliers_location (supplier_id INT NOT NULL, location_id INT NOT NULL, buyer_id INT DEFAULT NULL, INDEX IDX_BF0D152B2ADD6D8C (supplier_id), INDEX IDX_BF0D152B64D218E (location_id), INDEX IDX_BF0D152B6C755722 (buyer_id), PRIMARY KEY(supplier_id, location_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE suppliers ADD CONSTRAINT FK_AC28B95C64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE suppliers ADD CONSTRAINT FK_AC28B95CF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE suppliers ADD CONSTRAINT FK_AC28B95C38248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('ALTER TABLE suppliers_location ADD CONSTRAINT FK_BF0D152B2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES suppliers (id)');
        $this->addSql('ALTER TABLE suppliers_location ADD CONSTRAINT FK_BF0D152B64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE suppliers_location ADD CONSTRAINT FK_BF0D152B6C755722 FOREIGN KEY (buyer_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE suppliers DROP FOREIGN KEY FK_AC28B95C64D218E');
        $this->addSql('ALTER TABLE suppliers DROP FOREIGN KEY FK_AC28B95CF92F3E70');
        $this->addSql('ALTER TABLE suppliers DROP FOREIGN KEY FK_AC28B95C38248176');
        $this->addSql('ALTER TABLE suppliers_location DROP FOREIGN KEY FK_BF0D152B2ADD6D8C');
        $this->addSql('ALTER TABLE suppliers_location DROP FOREIGN KEY FK_BF0D152B64D218E');
        $this->addSql('ALTER TABLE suppliers_location DROP FOREIGN KEY FK_BF0D152B6C755722');
        $this->addSql('DROP TABLE suppliers');
        $this->addSql('DROP TABLE suppliers_location');
    }
}
