<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151126170655 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE phone (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(25) NOT NULL, number VARCHAR(60) NOT NULL, professional TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE people_phone (people_id INT NOT NULL, phone_id INT NOT NULL, INDEX IDX_8F2CED9D3147C936 (people_id), INDEX IDX_8F2CED9D3B7323CB (phone_id), PRIMARY KEY(people_id, phone_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE people_phone ADD CONSTRAINT FK_8F2CED9D3147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE people_phone ADD CONSTRAINT FK_8F2CED9D3B7323CB FOREIGN KEY (phone_id) REFERENCES phone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD division_id INT DEFAULT NULL, ADD department_id INT DEFAULT NULL, ADD supervisor_id INT DEFAULT NULL, ADD username VARCHAR(255) NOT NULL, ADD discr VARCHAR(255) NOT NULL, ADD erp_login VARCHAR(8) DEFAULT NULL, ADD erp_employee_id INT DEFAULT NULL, ADD windows_login VARCHAR(40) DEFAULT NULL, ADD address_street1 VARCHAR(255) DEFAULT NULL, ADD address_street2 VARCHAR(255) DEFAULT NULL, ADD address_postal_code VARCHAR(20) DEFAULT NULL, ADD address_city VARCHAR(50) DEFAULT NULL, ADD address_town VARCHAR(50) DEFAULT NULL, ADD address_state VARCHAR(50) DEFAULT NULL, ADD address_country VARCHAR(2) DEFAULT NULL, DROP phone, DROP direct_phone, DROP home_phone, DROP mobile, DROP fax, DROP address, DROP counter, DROP last_login, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE salt salt VARCHAR(64) NOT NULL, CHANGE legacy_id legacy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64941859289 FOREIGN KEY (division_id) REFERENCES directory_division (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649AE80F5DF FOREIGN KEY (department_id) REFERENCES directory_department (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64919E9AC5F FOREIGN KEY (supervisor_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64941859289 ON user (division_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649AE80F5DF ON user (department_id)');
        $this->addSql('CREATE INDEX IDX_8D93D64919E9AC5F ON user (supervisor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE people_phone DROP FOREIGN KEY FK_8F2CED9D3B7323CB');
        $this->addSql('DROP TABLE phone');
        $this->addSql('DROP TABLE people_phone');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64941859289');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649AE80F5DF');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64919E9AC5F');
        $this->addSql('DROP INDEX IDX_8D93D64941859289 ON user');
        $this->addSql('DROP INDEX IDX_8D93D649AE80F5DF ON user');
        $this->addSql('DROP INDEX IDX_8D93D64919E9AC5F ON user');
        $this->addSql('ALTER TABLE user ADD phone VARCHAR(25) DEFAULT NULL COLLATE utf8_unicode_ci, ADD direct_phone VARCHAR(25) DEFAULT NULL COLLATE utf8_unicode_ci, ADD home_phone VARCHAR(25) DEFAULT NULL COLLATE utf8_unicode_ci, ADD mobile VARCHAR(25) DEFAULT NULL COLLATE utf8_unicode_ci, ADD fax VARCHAR(25) DEFAULT NULL COLLATE utf8_unicode_ci, ADD address LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, ADD counter INT NOT NULL, ADD last_login DATETIME NOT NULL, DROP division_id, DROP department_id, DROP supervisor_id, DROP username, DROP discr, DROP erp_login, DROP erp_employee_id, DROP windows_login, DROP address_street1, DROP address_street2, DROP address_postal_code, DROP address_city, DROP address_town, DROP address_state, DROP address_country, CHANGE salt salt VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE email email VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE legacy_id legacy_id INT NOT NULL');
    }
}
