<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230930113717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipment_shipping_record (id INT AUTO_INCREMENT NOT NULL, sso_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, incoterm_id INT DEFAULT NULL, forwarder_id INT DEFAULT NULL, carrier_id INT DEFAULT NULL, created_at DATETIME NOT NULL, loading_place VARCHAR(100) DEFAULT NULL, departure_place VARCHAR(100) DEFAULT NULL, arrival_place VARCHAR(100) DEFAULT NULL, modality VARCHAR(25) NOT NULL, notes VARCHAR(255) DEFAULT NULL, ship_authorization TINYINT(1) NOT NULL, status VARCHAR(255) NOT NULL, legacy_id INT NOT NULL, INDEX IDX_C7BC6A1C7843BFA4 (sso_id), INDEX IDX_C7BC6A1C9395C3F3 (customer_id), INDEX IDX_C7BC6A1C7055C866 (incoterm_id), INDEX IDX_C7BC6A1CE4DF36A3 (forwarder_id), INDEX IDX_C7BC6A1C21DFC797 (carrier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipment_shipping_record_cost (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, currency_id INT DEFAULT NULL, equipment_shipping_record_id INT DEFAULT NULL, cost_date DATETIME NOT NULL, type VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, legacy_id INT NOT NULL, INDEX IDX_FC77CFFEB03A8386 (created_by_id), INDEX IDX_FC77CFFE38248176 (currency_id), INDEX IDX_FC77CFFE2EB8E369 (equipment_shipping_record_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipment_shipping_record_files (id INT NOT NULL, equipment_shipping_record_id INT DEFAULT NULL, INDEX IDX_E8DF702E2EB8E369 (equipment_shipping_record_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipment_shipping_record_line (id INT AUTO_INCREMENT NOT NULL, equipment_record_id INT DEFAULT NULL, equipment_shipping_record_id INT NOT NULL, vessel_loading_date DATETIME DEFAULT NULL, estimated_arrival_date DATETIME DEFAULT NULL, actual_arrival_date DATETIME DEFAULT NULL, estimated_pick_up_date DATETIME DEFAULT NULL, legacy_id INT NOT NULL, INDEX IDX_3545EFF49FC03375 (equipment_record_id), INDEX IDX_3545EFF42EB8E369 (equipment_shipping_record_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE incoterm (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_order_factory (id INT AUTO_INCREMENT NOT NULL, order_line_id INT DEFAULT NULL, equipment_record_id INT DEFAULT NULL, commissioning TINYINT(1) NOT NULL, requested_delivery_date DATETIME DEFAULT NULL, factory_promised_delivery_date DATETIME DEFAULT NULL, legacy_id INT NOT NULL, INDEX IDX_C86AC101BB01DC09 (order_line_id), UNIQUE INDEX UNIQ_C86AC1019FC03375 (equipment_record_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_order_lines (id INT AUTO_INCREMENT NOT NULL, incoterm_id INT DEFAULT NULL, order_id INT DEFAULT NULL, purchase_order_accepted_date DATETIME DEFAULT NULL, inspection TINYINT(1) NOT NULL, incoterm_location VARCHAR(255) DEFAULT NULL, is_payment_term_valid TINYINT(1) NOT NULL, status VARCHAR(255) DEFAULT NULL, legacy_id INT NOT NULL, INDEX IDX_8894E9AF7055C866 (incoterm_id), INDEX IDX_8894E9AF8D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_order_transactions (id INT AUTO_INCREMENT NOT NULL, equipment_record_id INT DEFAULT NULL, invoice VARCHAR(255) DEFAULT NULL, legacy_id INT NOT NULL, UNIQUE INDEX UNIQ_7D161AFB9FC03375 (equipment_record_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE equipment_shipping_record ADD CONSTRAINT FK_C7BC6A1C7843BFA4 FOREIGN KEY (sso_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE equipment_shipping_record ADD CONSTRAINT FK_C7BC6A1C9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE equipment_shipping_record ADD CONSTRAINT FK_C7BC6A1C7055C866 FOREIGN KEY (incoterm_id) REFERENCES incoterm (id)');
        $this->addSql('ALTER TABLE equipment_shipping_record ADD CONSTRAINT FK_C7BC6A1CE4DF36A3 FOREIGN KEY (forwarder_id) REFERENCES freight_forwarders (id)');
        $this->addSql('ALTER TABLE equipment_shipping_record ADD CONSTRAINT FK_C7BC6A1C21DFC797 FOREIGN KEY (carrier_id) REFERENCES freight_forwarders (id)');
        $this->addSql('ALTER TABLE equipment_shipping_record_cost ADD CONSTRAINT FK_FC77CFFEB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE equipment_shipping_record_cost ADD CONSTRAINT FK_FC77CFFE38248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('ALTER TABLE equipment_shipping_record_cost ADD CONSTRAINT FK_FC77CFFE2EB8E369 FOREIGN KEY (equipment_shipping_record_id) REFERENCES equipment_shipping_record (id)');
        $this->addSql('ALTER TABLE equipment_shipping_record_files ADD CONSTRAINT FK_E8DF702E2EB8E369 FOREIGN KEY (equipment_shipping_record_id) REFERENCES equipment_shipping_record (id)');
        $this->addSql('ALTER TABLE equipment_shipping_record_files ADD CONSTRAINT FK_E8DF702EBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipment_shipping_record_line ADD CONSTRAINT FK_3545EFF49FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE equipment_shipping_record_line ADD CONSTRAINT FK_3545EFF42EB8E369 FOREIGN KEY (equipment_shipping_record_id) REFERENCES equipment_shipping_record (id)');
        $this->addSql('ALTER TABLE sales_order_factory ADD CONSTRAINT FK_C86AC101BB01DC09 FOREIGN KEY (order_line_id) REFERENCES sales_order_lines (id)');
        $this->addSql('ALTER TABLE sales_order_factory ADD CONSTRAINT FK_C86AC1019FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE sales_order_lines ADD CONSTRAINT FK_8894E9AF7055C866 FOREIGN KEY (incoterm_id) REFERENCES incoterm (id)');
        $this->addSql('ALTER TABLE sales_order_lines ADD CONSTRAINT FK_8894E9AF8D9F6D38 FOREIGN KEY (order_id) REFERENCES sales_orders (id)');
        $this->addSql('ALTER TABLE sales_order_transactions ADD CONSTRAINT FK_7D161AFB9FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');

        $this->addSql('ALTER TABLE equipment_records ADD delivered_country_id INT DEFAULT NULL, ADD emission_rating_id INT DEFAULT NULL, ADD work_order LONGTEXT DEFAULT NULL, ADD light TINYINT(1) NOT NULL, ADD combination_mode VARCHAR(255) DEFAULT NULL, ADD contract_fms VARCHAR(255) DEFAULT NULL, ADD tld_link TINYINT(1) NOT NULL, ADD sim_card_status_active TINYINT(1) NOT NULL, ADD fms_end_use_date DATETIME DEFAULT NULL, ADD fms_contract_length INT NOT NULL');
        $this->addSql('ALTER TABLE equipment_records ADD CONSTRAINT FK_EAE697A9ED7FE265 FOREIGN KEY (delivered_country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE equipment_records ADD CONSTRAINT FK_EAE697A915428FA8 FOREIGN KEY (emission_rating_id) REFERENCES emission_ratings (id)');
        $this->addSql('CREATE INDEX IDX_EAE697A9ED7FE265 ON equipment_records (delivered_country_id)');
        $this->addSql('CREATE INDEX IDX_EAE697A915428FA8 ON equipment_records (emission_rating_id)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES ("FEATURE_ODP_EDIT_SUPPORT")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES ("FEATURE_ODP_EDIT_QUALITY")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES ("FEATURE_ODP_EDIT_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                         WHERE feature.name = "FEATURE_ODP_EDIT_SUPPORT" AND user_group.name IN ("SUPERUSER", "ROLE_SUPPORT", "ROLE_PSM", "ROLE_PSE", "ROLE_PSA") ');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                         WHERE feature.name = "FEATURE_ODP_EDIT_QUALITY" AND user_group.name IN ("SUPERUSER", "ROLE_QE", "ROLE_QAM") ');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                         WHERE feature.name = "FEATURE_ODP_EDIT_ADMIN" AND user_group.name IN ("SUPERUSER") ');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_ESR_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_ESR_WRITE"
          AND user_group.name IN ("SUPERUSER", "ROLE_SA", "GG_TRANSPORT", "GG_ADMIN")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_shipping_record DROP FOREIGN KEY FK_C7BC6A1C7843BFA4');
        $this->addSql('ALTER TABLE equipment_shipping_record DROP FOREIGN KEY FK_C7BC6A1C9395C3F3');
        $this->addSql('ALTER TABLE equipment_shipping_record DROP FOREIGN KEY FK_C7BC6A1C7055C866');
        $this->addSql('ALTER TABLE equipment_shipping_record DROP FOREIGN KEY FK_C7BC6A1CE4DF36A3');
        $this->addSql('ALTER TABLE equipment_shipping_record DROP FOREIGN KEY FK_C7BC6A1C21DFC797');
        $this->addSql('ALTER TABLE equipment_shipping_record_cost DROP FOREIGN KEY FK_FC77CFFEB03A8386');
        $this->addSql('ALTER TABLE equipment_shipping_record_cost DROP FOREIGN KEY FK_FC77CFFE38248176');
        $this->addSql('ALTER TABLE equipment_shipping_record_cost DROP FOREIGN KEY FK_FC77CFFE2EB8E369');
        $this->addSql('ALTER TABLE equipment_shipping_record_files DROP FOREIGN KEY FK_E8DF702E2EB8E369');
        $this->addSql('ALTER TABLE equipment_shipping_record_files DROP FOREIGN KEY FK_E8DF702EBF396750');
        $this->addSql('ALTER TABLE equipment_shipping_record_line DROP FOREIGN KEY FK_3545EFF49FC03375');
        $this->addSql('ALTER TABLE equipment_shipping_record_line DROP FOREIGN KEY FK_3545EFF42EB8E369');
        $this->addSql('ALTER TABLE sales_order_factory DROP FOREIGN KEY FK_C86AC101BB01DC09');
        $this->addSql('ALTER TABLE sales_order_factory DROP FOREIGN KEY FK_C86AC1019FC03375');
        $this->addSql('ALTER TABLE sales_order_lines DROP FOREIGN KEY FK_8894E9AF7055C866');
        $this->addSql('ALTER TABLE sales_order_lines DROP FOREIGN KEY FK_8894E9AF8D9F6D38');
        $this->addSql('ALTER TABLE sales_order_transactions DROP FOREIGN KEY FK_7D161AFB9FC03375');
        $this->addSql('DROP TABLE equipment_shipping_record');
        $this->addSql('DROP TABLE equipment_shipping_record_cost');
        $this->addSql('DROP TABLE equipment_shipping_record_files');
        $this->addSql('DROP TABLE equipment_shipping_record_line');
        $this->addSql('DROP TABLE incoterm');
        $this->addSql('DROP TABLE sales_order_factory');
        $this->addSql('DROP TABLE sales_order_lines');
        $this->addSql('DROP TABLE sales_order_transactions');
        $this->addSql('ALTER TABLE evendors_news DROP FOREIGN KEY FK_41A3C7E6DE12AB56');
        $this->addSql('DROP INDEX idx_41a3c7e6de12ab56 ON evendors_news');
        $this->addSql('CREATE INDEX IDX_41A3C7E6B03A8386 ON evendors_news (created_by)');
        $this->addSql('ALTER TABLE evendors_news ADD CONSTRAINT FK_41A3C7E6DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE crab ADD corrective_action LONGTEXT DEFAULT NULL, CHANGE fixing_comments fixing_comments LONGTEXT DEFAULT NULL, CHANGE inspecting_comments inspecting_comments LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE files ADD status VARCHAR(255) DEFAULT NULL, CHANGE public public TINYINT(1) DEFAULT 0');
        $this->addSql('ALTER TABLE equipment_records DROP FOREIGN KEY FK_EAE697A9ED7FE265');
        $this->addSql('ALTER TABLE equipment_records DROP FOREIGN KEY FK_EAE697A915428FA8');
        $this->addSql('DROP INDEX IDX_EAE697A9ED7FE265 ON equipment_records');
        $this->addSql('DROP INDEX IDX_EAE697A915428FA8 ON equipment_records');
        $this->addSql('ALTER TABLE equipment_records DROP delivered_country_id, DROP emission_rating_id, DROP work_order, DROP light, DROP combination_mode, DROP contract_fms, DROP tld_link, DROP sim_card_status_active, DROP fms_end_use_date, DROP fms_contract_length');
        $this->addSql('DROP INDEX unique_category_per_business_unit ON directory_position_classification');
        $this->addSql('ALTER TABLE non_conformity_parts DROP FOREIGN KEY FK_A72719868EA30491');
        $this->addSql('DROP INDEX idx_a72719868ea30491 ON non_conformity_parts');
        $this->addSql('CREATE INDEX IDX_61A451DF8EA30491 ON non_conformity_parts (non_conformity_id)');
        $this->addSql('ALTER TABLE non_conformity_parts ADD CONSTRAINT FK_A72719868EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id)');
        $this->addSql('ALTER TABLE directory_location CHANGE erp_in_ln erp_in_ln TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE non_conformity_main_files DROP FOREIGN KEY FK_898746DD8EA30491');
        $this->addSql('DROP INDEX idx_898746dd8ea30491 ON non_conformity_main_files');
        $this->addSql('CREATE INDEX IDX_C9220AC78EA30491 ON non_conformity_main_files (non_conformity_id)');
        $this->addSql('ALTER TABLE non_conformity_main_files ADD CONSTRAINT FK_898746DD8EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id)');
        $this->addSql('ALTER TABLE non_conformity CHANGE safety safety TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE supplier_corrective_action_request CHANGE description description LONGTEXT DEFAULT NULL');
    }
}
