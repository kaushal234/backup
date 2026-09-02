<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171120084252 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE extranet_user_acl (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', extranet_user_id INT NOT NULL COMMENT \'(DC2Type:integer)\', crt_id INT NOT NULL COMMENT \'(DC2Type:integer)\', extranet_user_group_id INT NOT NULL COMMENT \'(DC2Type:integer)\', c_del VARCHAR(3) DEFAULT NULL COMMENT \'(DC2Type:string)\', legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_3688504CD2CDD54B (extranet_user_id), INDEX IDX_3688504CC1ED4411 (crt_id), INDEX IDX_3688504CB449A9E8 (extranet_user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE extranet_user_favorite (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', extranet_user_id INT NOT NULL COMMENT \'(DC2Type:integer)\', aero_username VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', part_number VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', description VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_22D8FE76D2CDD54B (extranet_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE extranet_user_profile (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', country_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', customer INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', erp_location INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', extranet_user_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', type VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', gender VARCHAR(3) DEFAULT NULL COMMENT \'(DC2Type:string)\', lastname VARCHAR(50) NOT NULL COMMENT \'(DC2Type:string)\', firstname VARCHAR(50) NOT NULL COMMENT \'(DC2Type:string)\', division VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', department VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', job_title VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', language VARCHAR(2) DEFAULT NULL COMMENT \'(DC2Type:string)\', email VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', legacy_address TEXT NOT NULL, legacy_shipping_address TEXT NOT NULL, counter INT NOT NULL COMMENT \'(DC2Type:integer)\', last_login DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', customer_carrier_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', company_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', note VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', shipping_account_number VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', requestor_number VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', employee_number VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', seq_id VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', archived TINYINT(1) NOT NULL, legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', address_street1 VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', address_street2 VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', address_postal_code VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\', address_city VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', address_town VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', address_state VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', shipping_address_country VARCHAR(2) DEFAULT NULL COMMENT \'(DC2Type:string)\', shipping_address_street1 VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', shipping_address_street2 VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', shipping_address_postal_code VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\', shipping_address_city VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', shipping_address_town VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', shipping_address_state VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', UNIQUE INDEX UNIQ_4382446BE7927C74 (email), INDEX IDX_4382446BF92F3E70 (country_id), INDEX IDX_4382446B81398E09 (customer), INDEX IDX_4382446BD746BD8 (erp_location), UNIQUE INDEX UNIQ_4382446BD2CDD54B (extranet_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE extranet_user_profile_phone (extranet_user_profile_id INT NOT NULL COMMENT \'(DC2Type:integer)\', phone_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_34BB3778B56089BF (extranet_user_profile_id), INDEX IDX_34BB37783B7323CB (phone_id), PRIMARY KEY(extranet_user_profile_id, phone_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE extranet_user_group (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', name VARCHAR(50) NOT NULL COMMENT \'(DC2Type:string)\', description VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', UNIQUE INDEX UNIQ_1190F2065E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE extranet_user_acl ADD CONSTRAINT FK_3688504CD2CDD54B FOREIGN KEY (extranet_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE extranet_user_acl ADD CONSTRAINT FK_3688504CC1ED4411 FOREIGN KEY (crt_id) REFERENCES customer_relationship_teams (id)');
        $this->addSql('ALTER TABLE extranet_user_acl ADD CONSTRAINT FK_3688504CB449A9E8 FOREIGN KEY (extranet_user_group_id) REFERENCES extranet_user_group (id)');
        $this->addSql('ALTER TABLE extranet_user_favorite ADD CONSTRAINT FK_22D8FE76D2CDD54B FOREIGN KEY (extranet_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE extranet_user_profile ADD CONSTRAINT FK_4382446BF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE extranet_user_profile ADD CONSTRAINT FK_4382446B81398E09 FOREIGN KEY (customer) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE extranet_user_profile ADD CONSTRAINT FK_4382446BD746BD8 FOREIGN KEY (erp_location) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE extranet_user_profile ADD CONSTRAINT FK_4382446BD2CDD54B FOREIGN KEY (extranet_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE extranet_user_profile_phone ADD CONSTRAINT FK_34BB3778B56089BF FOREIGN KEY (extranet_user_profile_id) REFERENCES extranet_user_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE extranet_user_profile_phone ADD CONSTRAINT FK_34BB37783B7323CB FOREIGN KEY (phone_id) REFERENCES phone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD extranet_user_profile_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE business_unit_id business_unit_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE position_id position_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE division_id division_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE department_id department_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE supervisor_id supervisor_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE email email VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE firstname firstname VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE lastname lastname VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE nickname nickname VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE job_title job_title VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE photo photo VARCHAR(100) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE legacy_id legacy_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE erp_login erp_login VARCHAR(8) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE erp_employee_id erp_employee_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE windows_login windows_login VARCHAR(40) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_street1 address_street1 VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_street2 address_street2 VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_postal_code address_postal_code VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_city address_city VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_town address_town VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_state address_state VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_country address_country VARCHAR(2) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE token token VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE locale locale VARCHAR(2) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B56089BF FOREIGN KEY (extranet_user_profile_id) REFERENCES extranet_user_profile (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649B56089BF ON user (extranet_user_profile_id)');
        $this->addSql('INSERT INTO extranet_user_group (name, description) VALUES
                    ("acl_SCM", "Access to Boeing Service Contract information"),
                    ("acl_CCR", "Access to CCR module (Fedex/AF only)"),
                    ("acl_EDC", "Access to shared Boeing Service Contract information"),
                    ("role_TOC", "Access to TOC module"),
                    ("role_AP", "Account payable"),
                    ("role_buyer", "Buyer"),
                    ("fl_NO_SB_NOT", "Do not send SB notification (prior to SB 3)"),
                    ("NOTIFICATION_SB3", "Send SB notification (SB3 only)"),
                    ("role_ST", "Service Technician"),
                    ("acl_EPARTS", "Access to eParts module"),
                    ("fl_NOT_SPR_INV", "Send SPR INVOICE notification"),
                    ("fl_NOT_SPR_ORDER_ACK", "Send SPR ORDER ACKNOWLEDGMENT notification"),
                    ("fl_NOT_SPR_PS", "Send SPR PACKING SLIP notification"),
                    ("fl_NOT_TOC", "Send TOC notification"),
                    ("fl_NOT_WTOC", "Send Weekly TOC notification")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B56089BF');
        $this->addSql('ALTER TABLE extranet_user_profile_phone DROP FOREIGN KEY FK_34BB3778B56089BF');
        $this->addSql('ALTER TABLE extranet_user_acl DROP FOREIGN KEY FK_3688504CB449A9E8');
        $this->addSql('DROP TABLE extranet_user_acl');
        $this->addSql('DROP TABLE extranet_user_favorite');
        $this->addSql('DROP TABLE extranet_user_profile');
        $this->addSql('DROP TABLE extranet_user_profile_phone');
        $this->addSql('DROP TABLE extranet_user_group');
        $this->addSql('DROP INDEX IDX_8D93D649B56089BF ON user');
        $this->addSql('ALTER TABLE user DROP extranet_user_profile_id, CHANGE business_unit_id business_unit_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE position_id position_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE division_id division_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE department_id department_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE supervisor_id supervisor_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE token token VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE firstname firstname VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE lastname lastname VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE email email VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE nickname nickname VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE job_title job_title VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE photo photo VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE erp_login erp_login VARCHAR(8) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE erp_employee_id erp_employee_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE windows_login windows_login VARCHAR(40) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE locale locale VARCHAR(2) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE legacy_id legacy_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE address_country address_country VARCHAR(2) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_street1 address_street1 VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_street2 address_street2 VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_postal_code address_postal_code VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_city address_city VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_town address_town VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_state address_state VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
    }
}
