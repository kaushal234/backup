<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171205103047 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE equipment_records (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', buyer_id INT NOT NULL COMMENT \'(DC2Type:integer)\', end_user_id INT NOT NULL COMMENT \'(DC2Type:integer)\', parent_equipment_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', serial_number VARCHAR(20) NOT NULL COMMENT \'(DC2Type:string)\', customer_serial_number VARCHAR(30) DEFAULT NULL COMMENT \'(DC2Type:string)\', model VARCHAR(30) DEFAULT NULL COMMENT \'(DC2Type:string)\', type VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', location VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\', legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_EAE697A96C755722 (buyer_id), INDEX IDX_EAE697A932A1827C (end_user_id), INDEX IDX_EAE697A9111B4D0 (parent_equipment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE equipment_records ADD CONSTRAINT FK_EAE697A96C755722 FOREIGN KEY (buyer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE equipment_records ADD CONSTRAINT FK_EAE697A932A1827C FOREIGN KEY (end_user_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE equipment_records ADD CONSTRAINT FK_EAE697A9111B4D0 FOREIGN KEY (parent_equipment_id) REFERENCES equipment_records (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records DROP FOREIGN KEY FK_EAE697A9111B4D0');
        $this->addSql('DROP TABLE equipment_records');
    }
}
