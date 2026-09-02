<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201126222341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE IF EXISTS account_receivable_overview');
        $this->addSql('DROP TABLE IF EXISTS manufacturing_margin_synthesis');
        $this->addSql('ALTER TABLE countries CHANGE public public TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE dms CHANGE description description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE equipment_accidents_files CHANGE accident_id accident_id INT NOT NULL');
        $this->addSql('ALTER TABLE equipment_follow_up_reports DROP FOREIGN KEY FK_99D753AF16FE72E1');
        $this->addSql('ALTER TABLE equipment_follow_up_reports DROP FOREIGN KEY FK_99D753AF23C256BD');
        $this->addSql('ALTER TABLE equipment_follow_up_reports DROP FOREIGN KEY FK_99D753AF779AA16B');
        $this->addSql('ALTER TABLE equipment_follow_up_reports DROP FOREIGN KEY FK_99D753AF9FC03375');
        $this->addSql('ALTER TABLE equipment_follow_up_reports DROP FOREIGN KEY FK_99D753AFDE12AB56');
        $this->addSql('ALTER TABLE equipment_follow_up_reports DROP FOREIGN KEY FK_99D753AFF19CA0D9');
        $this->addSql('DROP INDEX idx_99d753afde12ab56 ON equipment_follow_up_reports');
        $this->addSql('CREATE INDEX IDX_46C2A11DDE12AB56 ON equipment_follow_up_reports (created_by)');
        $this->addSql('DROP INDEX idx_99d753af16fe72e1 ON equipment_follow_up_reports');
        $this->addSql('CREATE INDEX IDX_46C2A11D16FE72E1 ON equipment_follow_up_reports (updated_by)');
        $this->addSql('DROP INDEX idx_99d753af9fc03375 ON equipment_follow_up_reports');
        $this->addSql('CREATE INDEX IDX_46C2A11D9FC03375 ON equipment_follow_up_reports (equipment_record_id)');
        $this->addSql('DROP INDEX idx_99d753aff19ca0d9 ON equipment_follow_up_reports');
        $this->addSql('CREATE INDEX IDX_46C2A11DF19CA0D9 ON equipment_follow_up_reports (operational_status_id)');
        $this->addSql('DROP INDEX uniq_99d753af779aa16b ON equipment_follow_up_reports');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_46C2A11D779AA16B ON equipment_follow_up_reports (equipment_accident_id)');
        $this->addSql('DROP INDEX uniq_99d753af23c256bd ON equipment_follow_up_reports');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_46C2A11D23C256BD ON equipment_follow_up_reports (equipment_maintenance_id)');
        $this->addSql('ALTER TABLE equipment_follow_up_reports ADD CONSTRAINT FK_99D753AF16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE equipment_follow_up_reports ADD CONSTRAINT FK_99D753AF23C256BD FOREIGN KEY (equipment_maintenance_id) REFERENCES equipment_maintenances (id)');
        $this->addSql('ALTER TABLE equipment_follow_up_reports ADD CONSTRAINT FK_99D753AF779AA16B FOREIGN KEY (equipment_accident_id) REFERENCES equipment_accidents (id)');
        $this->addSql('ALTER TABLE equipment_follow_up_reports ADD CONSTRAINT FK_99D753AF9FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE equipment_follow_up_reports ADD CONSTRAINT FK_99D753AFDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE equipment_follow_up_reports ADD CONSTRAINT FK_99D753AFF19CA0D9 FOREIGN KEY (operational_status_id) REFERENCES unit_operational_statuses (id)');
        $this->addSql('ALTER TABLE equipment_hourmeter_resets DROP FOREIGN KEY FK_E30ABC189FC03375');
        $this->addSql('ALTER TABLE equipment_hourmeter_resets DROP FOREIGN KEY FK_E30ABC18DE12AB56');
        $this->addSql('DROP INDEX idx_e30abc18de12ab56 ON equipment_hourmeter_resets');
        $this->addSql('CREATE INDEX IDX_9617CDB4DE12AB56 ON equipment_hourmeter_resets (created_by)');
        $this->addSql('DROP INDEX idx_e30abc189fc03375 ON equipment_hourmeter_resets');
        $this->addSql('CREATE INDEX IDX_9617CDB49FC03375 ON equipment_hourmeter_resets (equipment_record_id)');
        $this->addSql('ALTER TABLE equipment_hourmeter_resets ADD CONSTRAINT FK_E30ABC189FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE equipment_hourmeter_resets ADD CONSTRAINT FK_E30ABC18DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE equipment_maintenance_files CHANGE maintenance_id maintenance_id INT NOT NULL');
        $this->addSql('ALTER TABLE ext_translations CHANGE object_class object_class VARCHAR(191) NOT NULL');
        $this->addSql('ALTER TABLE extranet_user_profile DROP FOREIGN KEY FK_4382446BD2CDD54B');
        $this->addSql('DROP INDEX UNIQ_4382446BD2CDD54B ON extranet_user_profile');
        $this->addSql('ALTER TABLE extranet_user_profile DROP extranet_user_id, DROP last_login');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE forecast_closures DROP FOREIGN KEY FK_B6C68D9278A5D405');
        $this->addSql('DROP INDEX fk_b6c68d9278a5d405 ON forecast_closures');
        $this->addSql('CREATE INDEX IDX_B6C68D9278A5D405 ON forecast_closures (competitor_id)');
        $this->addSql('ALTER TABLE forecast_closures ADD CONSTRAINT FK_B6C68D9278A5D405 FOREIGN KEY (competitor_id) REFERENCES competitors (id)');
        $this->addSql('ALTER TABLE manufacturing_families ADD CONSTRAINT FK_74B44D0CBF396750 FOREIGN KEY (id) REFERENCES families (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_family_dms CHANGE family_id family_id INT NOT NULL');
        $this->addSql('ALTER TABLE product_standard_item DROP FOREIGN KEY FK_32F41DE4584665A');
        $this->addSql('ALTER TABLE product_standard_item DROP FOREIGN KEY FK_32F41DEC7AF27D2');
        $this->addSql('ALTER TABLE product_standard_item CHANGE standard_item standard_item VARCHAR(30) NOT NULL');
        $this->addSql('DROP INDEX idx_32f41de4584665a ON product_standard_item');
        $this->addSql('CREATE INDEX IDX_DADFD3514584665A ON product_standard_item (product_id)');
        $this->addSql('DROP INDEX idx_32f41dec7af27d2 ON product_standard_item');
        $this->addSql('CREATE INDEX IDX_DADFD351C7AF27D2 ON product_standard_item (factory_id)');
        $this->addSql('ALTER TABLE product_standard_item ADD CONSTRAINT FK_32F41DE4584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE product_standard_item ADD CONSTRAINT FK_32F41DEC7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A58D77FA8');
        $this->addSql('DROP INDEX fk_b3ba5a5a58d77fa8 ON products');
        $this->addSql('CREATE INDEX IDX_B3BA5A5A58D77FA8 ON products (manufacturing_family_id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A58D77FA8 FOREIGN KEY (manufacturing_family_id) REFERENCES manufacturing_families (id)');
        $this->addSql('ALTER TABLE sales_areas DROP FOREIGN KEY FK_AEF403EA7843BFA4');
        $this->addSql('ALTER TABLE sales_areas DROP FOREIGN KEY FK_AEF403EA9C54D4BF');
        $this->addSql('ALTER TABLE sales_areas DROP FOREIGN KEY FK_AEF403EAF92F3E70');
        $this->addSql('DROP INDEX unique_asm_per_country_and_network ON sales_areas');
        $this->addSql('CREATE UNIQUE INDEX unique_asm_per_country_and_sso ON sales_areas (country_id, asm_id, sso_id)');
        $this->addSql('ALTER TABLE sales_areas ADD CONSTRAINT FK_AEF403EA7843BFA4 FOREIGN KEY (sso_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE sales_areas ADD CONSTRAINT FK_AEF403EA9C54D4BF FOREIGN KEY (asm_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sales_areas ADD CONSTRAINT FK_AEF403EAF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE sales_forecasts CHANGE master_sales_forecast_id master_sales_forecast_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transportation_notes DROP FOREIGN KEY FK_7FA126C3F92F3E70');
        $this->addSql('DROP INDEX uniq_7fa126c3f92f3e70 ON transportation_notes');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_191CFD27F92F3E70 ON transportation_notes (country_id)');
        $this->addSql('ALTER TABLE transportation_notes ADD CONSTRAINT FK_7FA126C3F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE training_subscription DROP FOREIGN KEY FK_BBD548DEB62DE735');
        $this->addSql('ALTER TABLE training_subscription DROP FOREIGN KEY FK_BBD548DE18721C9D');
        $this->addSql('ALTER TABLE training_subscription ADD CONSTRAINT FK_BBD548DE12469DE2 FOREIGN KEY (category_id) REFERENCES trainings_categories (id)');
        $this->addSql('CREATE INDEX IDX_BBD548DE12469DE2 ON training_subscription (category_id)');
        $this->addSql('DROP INDEX idx_bbd548de18721c9d ON training_subscription');
        $this->addSql('CREATE INDEX IDX_BBD548DEC54C8C93 ON training_subscription (type_id)');
        $this->addSql('ALTER TABLE training_subscription ADD CONSTRAINT FK_BBD548DE18721C9D FOREIGN KEY (type_id) REFERENCES trainings_types (id)');
        $this->addSql('ALTER TABLE training_subscription ADD CONSTRAINT FK_BBD548DEB62DE735 FOREIGN KEY (category_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE training_subscription DROP FOREIGN KEY FK_BBD548DEB62DE735');
        $this->addSql('DROP INDEX IDX_BBD548DEB62DE735 ON training_subscription');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account_receivable_overview (id INT NOT NULL, transaction_type_reference_id INT DEFAULT NULL, customer_erp_reference_id INT DEFAULT NULL, currency VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, total_value DOUBLE PRECISION NOT NULL, not_past_due DOUBLE PRECISION NOT NULL, past_due_one_month DOUBLE PRECISION NOT NULL, past_due_two_months DOUBLE PRECISION NOT NULL, past_due_three_months DOUBLE PRECISION NOT NULL, past_due_six_months DOUBLE PRECISION NOT NULL, past_due_more_than_six_months DOUBLE PRECISION NOT NULL, past_due_percentage INT NOT NULL, past_due_two_months_percentage INT NOT NULL, INDEX IDX_4E5AB0FADE219473 (transaction_type_reference_id), INDEX IDX_4E5AB0FAB8DD6B22 (customer_erp_reference_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE manufacturing_margin_synthesis (id INT NOT NULL, export_date DATETIME NOT NULL, finance_family VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, factory VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, product_type VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, quantity INT NOT NULL, currency VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, average_transfer_price INT NOT NULL, average_factory_discount INT NOT NULL, average_projected_direct_margin INT NOT NULL, average_actual_direct_margin INT NOT NULL, average_model_base_hours INT NOT NULL, average_industrial_incorporation_parameter INT NOT NULL, average_option_configuration_parameter_hours INT NOT NULL, average_unit_allocated_hours INT NOT NULL, average_factory_standard_efficiency_budget INT NOT NULL, average_actual_hours INT NOT NULL, average_factory_standard_efficiency_actual INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE countries CHANGE public public TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE dms CHANGE description description VARCHAR(65535) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE equipment_accidents_files CHANGE accident_id accident_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipment_follow_up_reports DROP FOREIGN KEY FK_46C2A11DDE12AB56');
        $this->addSql('ALTER TABLE equipment_follow_up_reports DROP FOREIGN KEY FK_46C2A11D16FE72E1');
        $this->addSql('ALTER TABLE equipment_follow_up_reports DROP FOREIGN KEY FK_46C2A11D9FC03375');
        $this->addSql('ALTER TABLE equipment_follow_up_reports DROP FOREIGN KEY FK_46C2A11DF19CA0D9');
        $this->addSql('ALTER TABLE equipment_follow_up_reports DROP FOREIGN KEY FK_46C2A11D779AA16B');
        $this->addSql('ALTER TABLE equipment_follow_up_reports DROP FOREIGN KEY FK_46C2A11D23C256BD');
        $this->addSql('DROP INDEX idx_46c2a11dde12ab56 ON equipment_follow_up_reports');
        $this->addSql('CREATE INDEX IDX_99D753AFDE12AB56 ON equipment_follow_up_reports (created_by)');
        $this->addSql('DROP INDEX idx_46c2a11d16fe72e1 ON equipment_follow_up_reports');
        $this->addSql('CREATE INDEX IDX_99D753AF16FE72E1 ON equipment_follow_up_reports (updated_by)');
        $this->addSql('DROP INDEX uniq_46c2a11d779aa16b ON equipment_follow_up_reports');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_99D753AF779AA16B ON equipment_follow_up_reports (equipment_accident_id)');
        $this->addSql('DROP INDEX idx_46c2a11d9fc03375 ON equipment_follow_up_reports');
        $this->addSql('CREATE INDEX IDX_99D753AF9FC03375 ON equipment_follow_up_reports (equipment_record_id)');
        $this->addSql('DROP INDEX uniq_46c2a11d23c256bd ON equipment_follow_up_reports');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_99D753AF23C256BD ON equipment_follow_up_reports (equipment_maintenance_id)');
        $this->addSql('DROP INDEX idx_46c2a11df19ca0d9 ON equipment_follow_up_reports');
        $this->addSql('CREATE INDEX IDX_99D753AFF19CA0D9 ON equipment_follow_up_reports (operational_status_id)');
        $this->addSql('ALTER TABLE equipment_follow_up_reports ADD CONSTRAINT FK_46C2A11DDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE equipment_follow_up_reports ADD CONSTRAINT FK_46C2A11D16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE equipment_follow_up_reports ADD CONSTRAINT FK_46C2A11D9FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE equipment_follow_up_reports ADD CONSTRAINT FK_46C2A11DF19CA0D9 FOREIGN KEY (operational_status_id) REFERENCES unit_operational_statuses (id)');
        $this->addSql('ALTER TABLE equipment_follow_up_reports ADD CONSTRAINT FK_46C2A11D779AA16B FOREIGN KEY (equipment_accident_id) REFERENCES equipment_accidents (id)');
        $this->addSql('ALTER TABLE equipment_follow_up_reports ADD CONSTRAINT FK_46C2A11D23C256BD FOREIGN KEY (equipment_maintenance_id) REFERENCES equipment_maintenances (id)');
        $this->addSql('ALTER TABLE equipment_hourmeter_resets DROP FOREIGN KEY FK_9617CDB4DE12AB56');
        $this->addSql('ALTER TABLE equipment_hourmeter_resets DROP FOREIGN KEY FK_9617CDB49FC03375');
        $this->addSql('DROP INDEX idx_9617cdb4de12ab56 ON equipment_hourmeter_resets');
        $this->addSql('CREATE INDEX IDX_E30ABC18DE12AB56 ON equipment_hourmeter_resets (created_by)');
        $this->addSql('DROP INDEX idx_9617cdb49fc03375 ON equipment_hourmeter_resets');
        $this->addSql('CREATE INDEX IDX_E30ABC189FC03375 ON equipment_hourmeter_resets (equipment_record_id)');
        $this->addSql('ALTER TABLE equipment_hourmeter_resets ADD CONSTRAINT FK_9617CDB4DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE equipment_hourmeter_resets ADD CONSTRAINT FK_9617CDB49FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE equipment_maintenance_files CHANGE maintenance_id maintenance_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ext_translations CHANGE object_class object_class VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE extranet_user_profile ADD extranet_user_id INT DEFAULT NULL, ADD last_login DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE extranet_user_profile ADD CONSTRAINT FK_4382446BD2CDD54B FOREIGN KEY (extranet_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4382446BD2CDD54B ON extranet_user_profile (extranet_user_id)');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items CHANGE description description VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE forecast_closures DROP FOREIGN KEY FK_B6C68D9278A5D405');
        $this->addSql('DROP INDEX idx_b6c68d9278a5d405 ON forecast_closures');
        $this->addSql('CREATE INDEX FK_B6C68D9278A5D405 ON forecast_closures (competitor_id)');
        $this->addSql('ALTER TABLE forecast_closures ADD CONSTRAINT FK_B6C68D9278A5D405 FOREIGN KEY (competitor_id) REFERENCES competitors (id)');
        $this->addSql('ALTER TABLE manufacturing_families DROP FOREIGN KEY FK_74B44D0CBF396750');
        $this->addSql('ALTER TABLE product_family_dms CHANGE family_id family_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_standard_item DROP FOREIGN KEY FK_DADFD3514584665A');
        $this->addSql('ALTER TABLE product_standard_item DROP FOREIGN KEY FK_DADFD351C7AF27D2');
        $this->addSql('ALTER TABLE product_standard_item CHANGE standard_item standard_item VARCHAR(30) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('DROP INDEX idx_dadfd3514584665a ON product_standard_item');
        $this->addSql('CREATE INDEX IDX_32F41DE4584665A ON product_standard_item (product_id)');
        $this->addSql('DROP INDEX idx_dadfd351c7af27d2 ON product_standard_item');
        $this->addSql('CREATE INDEX IDX_32F41DEC7AF27D2 ON product_standard_item (factory_id)');
        $this->addSql('ALTER TABLE product_standard_item ADD CONSTRAINT FK_DADFD3514584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE product_standard_item ADD CONSTRAINT FK_DADFD351C7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A58D77FA8');
        $this->addSql('DROP INDEX idx_b3ba5a5a58d77fa8 ON products');
        $this->addSql('CREATE INDEX FK_B3BA5A5A58D77FA8 ON products (manufacturing_family_id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A58D77FA8 FOREIGN KEY (manufacturing_family_id) REFERENCES manufacturing_families (id)');
        $this->addSql('ALTER TABLE sales_areas DROP FOREIGN KEY FK_AEF403EAF92F3E70');
        $this->addSql('ALTER TABLE sales_areas DROP FOREIGN KEY FK_AEF403EA9C54D4BF');
        $this->addSql('ALTER TABLE sales_areas DROP FOREIGN KEY FK_AEF403EA7843BFA4');
        $this->addSql('DROP INDEX unique_asm_per_country_and_sso ON sales_areas');
        $this->addSql('CREATE UNIQUE INDEX unique_asm_per_country_and_network ON sales_areas (country_id, asm_id, sso_id)');
        $this->addSql('ALTER TABLE sales_areas ADD CONSTRAINT FK_AEF403EAF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE sales_areas ADD CONSTRAINT FK_AEF403EA9C54D4BF FOREIGN KEY (asm_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sales_areas ADD CONSTRAINT FK_AEF403EA7843BFA4 FOREIGN KEY (sso_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE sales_forecasts CHANGE master_sales_forecast_id master_sales_forecast_id INT NOT NULL');
        $this->addSql('ALTER TABLE transportation_notes DROP FOREIGN KEY FK_191CFD27F92F3E70');
        $this->addSql('DROP INDEX uniq_191cfd27f92f3e70 ON transportation_notes');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7FA126C3F92F3E70 ON transportation_notes (country_id)');
        $this->addSql('ALTER TABLE transportation_notes ADD CONSTRAINT FK_191CFD27F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE training_subscription DROP FOREIGN KEY FK_BBD548DE12469DE2');
        $this->addSql('ALTER TABLE training_subscription DROP FOREIGN KEY FK_BBD548DE12469DE2');
        $this->addSql('ALTER TABLE training_subscription DROP FOREIGN KEY FK_BBD548DEC54C8C93');
        $this->addSql('DROP INDEX idx_bbd548de12469de2 ON training_subscription');
        $this->addSql('DROP INDEX idx_bbd548dec54c8c93 ON training_subscription');
        $this->addSql('CREATE INDEX IDX_BBD548DE18721C9D ON training_subscription (type_id)');
        $this->addSql('ALTER TABLE training_subscription ADD CONSTRAINT FK_BBD548DE12469DE2 FOREIGN KEY (category_id) REFERENCES trainings_categories (id)');
        $this->addSql('ALTER TABLE training_subscription ADD CONSTRAINT FK_BBD548DEC54C8C93 FOREIGN KEY (type_id) REFERENCES trainings_types (id)');
        $this->addSql('ALTER TABLE training_subscription ADD CONSTRAINT FK_BBD548DEB62DE735 FOREIGN KEY (category_id) REFERENCES customers (id)');
        $this->addSql('CREATE INDEX IDX_BBD548DEB62DE735 ON training_subscription (category_id)');
    }
}
