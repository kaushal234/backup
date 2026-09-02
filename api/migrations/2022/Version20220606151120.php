<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220606151120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'VWC / SCAR / NCR modules migrations';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE non_conformity_parts (id INT NOT NULL, non_conformity_id INT NOT NULL, reference VARCHAR(255) DEFAULT NULL, reference_number VARCHAR(255) DEFAULT NULL, serial_number VARCHAR(255) DEFAULT NULL, INDEX IDX_61A451DF8EA30491 (non_conformity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE non_conformity (id INT AUTO_INCREMENT NOT NULL, factory_id INT NOT NULL, process_id INT DEFAULT NULL, reported_by_id INT DEFAULT NULL, repair_approver_id INT DEFAULT NULL, currency_id INT DEFAULT NULL, created_at DATETIME NOT NULL, status VARCHAR(255) NOT NULL, hours INT DEFAULT NULL, problem VARCHAR(255) NOT NULL, short_description VARCHAR(255) NOT NULL, solution VARCHAR(255) DEFAULT NULL, responsible VARCHAR(255) DEFAULT NULL, purchase_order_number VARCHAR(255) DEFAULT NULL, rush TINYINT(1) NOT NULL, charge_vendor TINYINT(1) NOT NULL, failure_type VARCHAR(255) DEFAULT NULL, i_factor VARCHAR(255) NOT NULL, investigation VARCHAR(255) DEFAULT NULL, scrap TINYINT(1) NOT NULL, rework TINYINT(1) NOT NULL, first_article_inspection TINYINT(1) NOT NULL, use_as_is TINYINT(1) NOT NULL, derogation TINYINT(1) NOT NULL, return_vendor TINYINT(1) NOT NULL, charge_vendor_for_repair TINYINT(1) NOT NULL, supplier_corrective_action_request TINYINT(1) NOT NULL, internal_corrective_action_request TINYINT(1) NOT NULL, other TINYINT(1) NOT NULL, containment TINYINT(1) NOT NULL, action_comment VARCHAR(255) DEFAULT NULL, repair_approval_date DATETIME DEFAULT NULL, cost_breakdown VARCHAR(255) DEFAULT NULL, cost DOUBLE PRECISION DEFAULT NULL, work_order_reference VARCHAR(255) DEFAULT NULL, non_quality_cost DOUBLE PRECISION DEFAULT NULL, invoice_number VARCHAR(255) DEFAULT NULL, environmental_issue TINYINT(1) DEFAULT NULL, crab_id INT DEFAULT NULL, supplier_name VARCHAR(255) DEFAULT NULL, supplier_number VARCHAR(255) DEFAULT NULL, supplier_erp INT DEFAULT NULL, INDEX IDX_9726A49AC7AF27D2 (factory_id), INDEX IDX_9726A49A7EC2F574 (process_id), INDEX IDX_9726A49A71CE806 (reported_by_id), INDEX IDX_9726A49A6E271D4D (repair_approver_id), INDEX IDX_9726A49A38248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE non_conformity_product (non_conformity_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_1875A7BF8EA30491 (non_conformity_id), INDEX IDX_1875A7BF4584665A (product_id), PRIMARY KEY(non_conformity_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE non_conformity_files (id INT NOT NULL, non_conformity_id INT DEFAULT NULL, INDEX IDX_C852FE218EA30491 (non_conformity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE non_conformity_photo_files (id INT NOT NULL, non_conformity_id INT DEFAULT NULL, INDEX IDX_C9220AC78EA30491 (non_conformity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE non_quality_costs (id INT AUTO_INCREMENT NOT NULL, factory_id INT NOT NULL, default_costs INT NOT NULL, INDEX IDX_26904F7CC7AF27D2 (factory_id), UNIQUE INDEX unique_non_quality_cost_per_factory (factory_id, default_costs), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE process (id INT AUTO_INCREMENT NOT NULL, category VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_corrective_action_request (id INT AUTO_INCREMENT NOT NULL, factory_id INT NOT NULL, representative_id INT DEFAULT NULL, poster_id INT DEFAULT NULL, supplier_representative_id INT DEFAULT NULL, leader_id INT DEFAULT NULL, created_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, approved_at DATETIME DEFAULT NULL, i_factor VARCHAR(255) NOT NULL, short_description VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, issue_origin VARCHAR(255) DEFAULT NULL, corrective_action VARCHAR(255) DEFAULT NULL, commercial_agreement VARCHAR(255) DEFAULT NULL, verification_description VARCHAR(255) DEFAULT NULL, preventive_action VARCHAR(255) DEFAULT NULL, conclusion VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, status VARCHAR(255) NOT NULL, supplier_name VARCHAR(255) NOT NULL, supplier_number VARCHAR(255) NOT NULL, supplier_erp INT NOT NULL, INDEX IDX_33492477C7AF27D2 (factory_id), INDEX IDX_33492477FC3FF006 (representative_id), INDEX IDX_334924775BB66C05 (poster_id), INDEX IDX_3349247731BFF723 (supplier_representative_id), INDEX IDX_3349247773154ED4 (leader_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_corrective_action_request_files (id INT NOT NULL, supplier_corrective_action_request_id INT DEFAULT NULL, INDEX IDX_6E5A5406C917A344 (supplier_corrective_action_request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_corrective_action_request_main_files (id INT NOT NULL, supplier_corrective_action_request_id INT DEFAULT NULL, INDEX IDX_2EFE067FC917A344 (supplier_corrective_action_request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_corrective_action_requests_parts (id INT NOT NULL, supplier_corrective_action_request_id INT NOT NULL, INDEX IDX_10D93BFBC917A344 (supplier_corrective_action_request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendor_warranty_claim_files (id INT NOT NULL, vendor_warranty_claim_id INT DEFAULT NULL, INDEX IDX_DD07B0BB75B4C7D4 (vendor_warranty_claim_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendor_warranty_claim_ncr (id INT NOT NULL, non_conformity_id INT NOT NULL, INDEX IDX_A7CCDDD58EA30491 (non_conformity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendor_warranty_claim_status (id INT AUTO_INCREMENT NOT NULL, group_id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, position INT NOT NULL, INDEX IDX_35DFD4B8FE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendor_warranty_claim_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendor_warranty_claim_wc (id INT NOT NULL, warranty_claim_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendor_warranty_claims (id INT AUTO_INCREMENT NOT NULL, supplier_corrective_action_request_id INT DEFAULT NULL, type_id INT DEFAULT NULL, factory_id INT NOT NULL, poster_id INT NOT NULL, assignee_id INT DEFAULT NULL, status_id INT NOT NULL, currency_id INT DEFAULT NULL, scar_requested TINYINT(1) NOT NULL, status_updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, requested_supplier_action LONGTEXT NOT NULL, requested_credit_amount DOUBLE PRECISION DEFAULT NULL, supplier_return_merchandise_authorization VARCHAR(255) DEFAULT NULL, supplier_credit_note VARCHAR(255) DEFAULT NULL, supplier_credit_amount DOUBLE PRECISION DEFAULT NULL, actual_credit_amount DOUBLE PRECISION DEFAULT NULL, supplier_shipping_instruction VARCHAR(255) DEFAULT NULL, supplier_shipper_name VARCHAR(255) DEFAULT NULL, accepted TINYINT(1) NOT NULL, cost_breakdown VARCHAR(255) DEFAULT NULL, ship_back_defective_part TINYINT(1) NOT NULL, resolution VARCHAR(255) DEFAULT NULL, tracking_number VARCHAR(255) DEFAULT NULL, supplier_name VARCHAR(255) DEFAULT NULL, supplier_number VARCHAR(255) DEFAULT NULL, supplier_erp INT DEFAULT NULL, closed_at DATETIME DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_88ED6D08C917A344 (supplier_corrective_action_request_id), INDEX IDX_88ED6D08C54C8C93 (type_id), INDEX IDX_88ED6D08C7AF27D2 (factory_id), INDEX IDX_88ED6D085BB66C05 (poster_id), INDEX IDX_88ED6D0859EC7D60 (assignee_id), INDEX IDX_88ED6D086BF700BD (status_id), INDEX IDX_88ED6D0838248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendor_warranty_claims_parts (id INT NOT NULL, vendor_warranty_claim_id INT NOT NULL, serial_number VARCHAR(255) DEFAULT NULL, vendor_part_number VARCHAR(255) DEFAULT NULL, vendor_serial_number VARCHAR(255) DEFAULT NULL, failure_type VARCHAR(255) DEFAULT NULL, failure_system VARCHAR(255) DEFAULT NULL, ship TINYINT(1) NOT NULL, received_quantity INT DEFAULT NULL, INDEX IDX_A5BAA92E75B4C7D4 (vendor_warranty_claim_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE non_conformity_parts ADD CONSTRAINT FK_61A451DF8EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id)');
        $this->addSql('ALTER TABLE non_conformity_parts ADD CONSTRAINT FK_61A451DFBF396750 FOREIGN KEY (id) REFERENCES parts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE non_conformity ADD CONSTRAINT FK_9726A49AC7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE non_conformity ADD CONSTRAINT FK_9726A49A7EC2F574 FOREIGN KEY (process_id) REFERENCES process (id)');
        $this->addSql('ALTER TABLE non_conformity ADD CONSTRAINT FK_9726A49A71CE806 FOREIGN KEY (reported_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE non_conformity ADD CONSTRAINT FK_9726A49A6E271D4D FOREIGN KEY (repair_approver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE non_conformity ADD CONSTRAINT FK_9726A49A38248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('ALTER TABLE non_conformity_product ADD CONSTRAINT FK_1875A7BF8EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE non_conformity_product ADD CONSTRAINT FK_1875A7BF4584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE non_conformity_files ADD CONSTRAINT FK_C852FE218EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id)');
        $this->addSql('ALTER TABLE non_conformity_files ADD CONSTRAINT FK_C852FE21BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE non_conformity_photo_files ADD CONSTRAINT FK_C9220AC78EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id)');
        $this->addSql('ALTER TABLE non_conformity_photo_files ADD CONSTRAINT FK_C9220AC7BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE non_quality_costs ADD CONSTRAINT FK_26904F7CC7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE supplier_corrective_action_request ADD CONSTRAINT FK_33492477C7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE supplier_corrective_action_request ADD CONSTRAINT FK_33492477FC3FF006 FOREIGN KEY (representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE supplier_corrective_action_request ADD CONSTRAINT FK_334924775BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE supplier_corrective_action_request ADD CONSTRAINT FK_3349247731BFF723 FOREIGN KEY (supplier_representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE supplier_corrective_action_request ADD CONSTRAINT FK_3349247773154ED4 FOREIGN KEY (leader_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE supplier_corrective_action_request_files ADD CONSTRAINT FK_6E5A5406C917A344 FOREIGN KEY (supplier_corrective_action_request_id) REFERENCES supplier_corrective_action_request (id)');
        $this->addSql('ALTER TABLE supplier_corrective_action_request_files ADD CONSTRAINT FK_6E5A5406BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supplier_corrective_action_request_main_files ADD CONSTRAINT FK_2EFE067FC917A344 FOREIGN KEY (supplier_corrective_action_request_id) REFERENCES supplier_corrective_action_request (id)');
        $this->addSql('ALTER TABLE supplier_corrective_action_request_main_files ADD CONSTRAINT FK_2EFE067FBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supplier_corrective_action_requests_parts ADD CONSTRAINT FK_10D93BFBC917A344 FOREIGN KEY (supplier_corrective_action_request_id) REFERENCES supplier_corrective_action_request (id)');
        $this->addSql('ALTER TABLE supplier_corrective_action_requests_parts ADD CONSTRAINT FK_10D93BFBBF396750 FOREIGN KEY (id) REFERENCES parts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vendor_warranty_claim_files ADD CONSTRAINT FK_DD07B0BB75B4C7D4 FOREIGN KEY (vendor_warranty_claim_id) REFERENCES vendor_warranty_claims (id)');
        $this->addSql('ALTER TABLE vendor_warranty_claim_files ADD CONSTRAINT FK_DD07B0BBBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vendor_warranty_claim_ncr ADD CONSTRAINT FK_A7CCDDD58EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id)');
        $this->addSql('ALTER TABLE vendor_warranty_claim_ncr ADD CONSTRAINT FK_A7CCDDD5BF396750 FOREIGN KEY (id) REFERENCES vendor_warranty_claims (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vendor_warranty_claim_status ADD CONSTRAINT FK_35DFD4B8FE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE vendor_warranty_claim_wc ADD CONSTRAINT FK_3F916DA8BF396750 FOREIGN KEY (id) REFERENCES vendor_warranty_claims (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vendor_warranty_claims ADD CONSTRAINT FK_88ED6D08C917A344 FOREIGN KEY (supplier_corrective_action_request_id) REFERENCES supplier_corrective_action_request (id)');
        $this->addSql('ALTER TABLE vendor_warranty_claims ADD CONSTRAINT FK_88ED6D08C54C8C93 FOREIGN KEY (type_id) REFERENCES vendor_warranty_claim_type (id)');
        $this->addSql('ALTER TABLE vendor_warranty_claims ADD CONSTRAINT FK_88ED6D08C7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE vendor_warranty_claims ADD CONSTRAINT FK_88ED6D085BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vendor_warranty_claims ADD CONSTRAINT FK_88ED6D0859EC7D60 FOREIGN KEY (assignee_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vendor_warranty_claims ADD CONSTRAINT FK_88ED6D086BF700BD FOREIGN KEY (status_id) REFERENCES vendor_warranty_claim_status (id)');
        $this->addSql('ALTER TABLE vendor_warranty_claims ADD CONSTRAINT FK_88ED6D0838248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('ALTER TABLE vendor_warranty_claims_parts ADD CONSTRAINT FK_A5BAA92E75B4C7D4 FOREIGN KEY (vendor_warranty_claim_id) REFERENCES vendor_warranty_claims (id)');
        $this->addSql('ALTER TABLE vendor_warranty_claims_parts ADD CONSTRAINT FK_A5BAA92EBF396750 FOREIGN KEY (id) REFERENCES parts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE parts CHANGE created_by_id created_by_id INT DEFAULT NULL');

        $this->addSql('CREATE INDEX IDX_88ED6D085D13417F ON vendor_warranty_claims (closed_at)');
        $this->addSql('CREATE INDEX IDX_88ED6D084DF2CD80 ON vendor_warranty_claims (supplier_number)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES
            ("FEATURE_NON_CONFORMITY_EDIT"),
            ("FEATURE_NON_CONFORMITY_DELETE"),
            ("FEATURE_NON_CONFORMITY_FILE_DELETE"),
            ("FEATURE_SCAR_STATUS_ADMIN"),
            ("FEATURE_SCAR_STATUS"),
            ("FEATURE_SCAR_COMMENT"),
            ("FEATURE_SCAR_FILE_DELETE"),
            ("FEATURE_SCAR_EDIT_FULL"),
            ("FEATURE_SCAR_DELETE"),
            ("FEATURE_SCAR_DELETE_FACTORY"),
            ("FEATURE_NON_CONFORMITY_STATUS"),
            ("FEATURE_NON_QUALITY_COSTS_ADMIN"),
            ("FEATURE_NCR_VENDOR_WARRANTY_CLAIM_CREATE"),
            ("FEATURE_WC_VENDOR_WARRANTY_CLAIM_CREATE"),
            ("FEATURE_VENDOR_WARRANTY_CLAIM_EDIT"),
            ("FEATURE_VWC_EDIT_FINANCE"),
            ("FEATURE_VENDOR_WARRANTY_CLAIM_EDIT_PARTS"),
            ("FEATURE_VENDOR_WARRANTY_CLAIM_DELETE"),
            ("FEATURE_VENDOR_WARRANTY_CLAIM_FILE_DELETE"),
            ("FEATURE_VENDOR_WARRANTY_CLAIM_EDIT_SHIPPING"),
            ("FEATURE_SCAR_READ"),
            ("FEATURE_VENDOR_WARRANTY_CLAIM_READ")
        ');

        $this->addSql('INSERT IGNORE INTO feature_authorized_application (feature_id, authorized_application_id)
                   SELECT feature.id, authorized_application.id
                     FROM feature, authorized_application
                    WHERE feature.name = "FEATURE_SCAR_READ"
                      AND authorized_application.name = "evendors"');

        $this->addSql('INSERT IGNORE INTO feature_authorized_application (feature_id, authorized_application_id)
                   SELECT feature.id, authorized_application.id
                     FROM feature, authorized_application
                    WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_READ"
                      AND authorized_application.name = "evendors"');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_NON_CONFORMITY_EDIT"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM", "GG_ACCT", "GG_QUALITY", "GG_ADMIN", "GG_PUR", "GG_PRODUCTION")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_NON_CONFORMITY_DELETE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_NON_CONFORMITY_FILE_DELETE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM", "ROLE_QE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SCAR_STATUS_ADMIN"
                          AND user_group.name IN ("SUPERUSER", "ROLE_CEO", "ROLE_COO", "ROLE_QAM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SCAR_COMMENT"
                          AND user_group.name IN ("SUPERUSER", "GG_ADMIN", "ROLE_QAM", "ROLE_RME", "ROLE_QE", "ROLE_MLM", "GG_PUR")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SCAR_FILE_DELETE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_RME", "ROLE_QE", "ROLE_QAM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SCAR_EDIT_FULL"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM", "ROLE_CEO", "ROLE_COO")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SCAR_STATUS"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM", "ROLE_QA", "ROLE_QE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SCAR_DELETE"
                          AND user_group.name IN ("SUPERUSER", "GG_ADMIN")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SCAR_DELETE_FACTORY"
                          AND user_group.name IN ("ROLE_QAM", "ROLE_QE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_NON_CONFORMITY_STATUS"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM", "ROLE_QE", "NCR")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_NON_QUALITY_COSTS_ADMIN"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM", "ROLE_MLM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_NCR_VENDOR_WARRANTY_CLAIM_CREATE"
                          AND user_group.name IN ("SUPERUSER", "GG_ADMIN", "GG_QUALITY")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_WC_VENDOR_WARRANTY_CLAIM_CREATE"
                          AND user_group.name IN ("SUPERUSER", "GG_ADMIN", "GG_SUPPORT", "GG_PARTS", "ROLE_RME")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_EDIT"
                          AND user_group.name IN ("SUPERUSER", "GG_PUR", "GG_ACCT", "GG_WAREHOUSE", "ROLE_PSM", "ROLE_PSE", "ROLE_PSA", "ROLE_RME", "ROLE_PSA")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_VWC_EDIT_FINANCE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_AP", "ROLE_FC", "ROLE_QAM", "ROLE_BYR")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_EDIT_PARTS"
                          AND user_group.name IN ("SUPERUSER", "GG_PUR", "GG_ACCT", "GG_QUALITY", "GG_WAREHOUSE", "ROLE_PSA", "ROLE_PSE", "ROLE_PSM", "ROLE_RME")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_DELETE"
                          AND user_group.name IN ("SUPERUSER")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_FILE_DELETE"
                          AND user_group.name IN ("SUPERUSER", "GG_PUR", "GG_QUALITY")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_EDIT_SHIPPING"
                          AND user_group.name IN ("SUPERUSER", "GG_PUR", "ROLE_QAM", "GG_PARTS", "GG_WAREHOUSE")');

        $this->addSql("INSERT IGNORE INTO process (category, description) VALUES
            ('Engineering', 'Drawing Error / Issue'),
            ('Engineering', 'Quantity Error / Issue'),
            ('Engineering', 'Design Issue'),
            ('Engineering', 'BOM / CBOM Issue'),
            ('Engineering', 'Revision Issue'),
            ('Production', 'Part issued but missing or lost after delivery'),
            ('Production', 'Part damaged in Production Sub Assy'),
            ('Production', 'Part Assembled Incorrectly in Production'),
            ('Production', 'Part Installed Incorrectly in Production Assy / Final Assy'),
            ('Production', 'NCR entry Error'),
            ('Production', 'Wrong Part Requested / Arrived'),
            ('Purchasing', 'Arrived Damaged from Supplier'),
            ('Purchasing', 'Incorrect Part Number Ordered / Incorrect Part Arrived'),
            ('Purchasing', 'Incorrect Configuration or Revision Arrived'),
            ('Purchasing', 'Not to Spec / Not to Print'),
            ('Warehouse', 'Receiving Error'),
            ('Warehouse', 'Picking Error - PN'),
            ('Warehouse', 'Damaged Part Arrived to Production'),
            ('Warehouse', 'Incomplete Part / Assy Delivered to Production'),
            ('Warehouse', 'Incomplete Quantity Delivered to Production'),
            ('Warehouse', 'Part Issued to Production'),
            ('Warehouse', 'Part Arrived to Production'),
            ('Warehouse', 'Freight Damage');
        ");

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT IGNORE INTO vendor_warranty_claim_status (name, description, position, group_id) VALUES
            ('QA ANALYSIS', 'Default status for a VWC created without a vendor ID or a validated charge back amount', 1, 35),
            ('PENDING', 'PURCHASING to review VWC. Not visible to vendor yet.', 2, 71),
            ('VENDOR_TO_RESPOND', 'VENDOR to respond. VWC is visible to vendor. Waiting for agreement and Return Merchandise Authorization Number as confirmation from vendor', 3, 71),
            ('REVIEW_VENDOR_RESPONSE', 'PURCHASING to review vendor response before proceeding with VWC', 4, 71),
            ('CREATE_PO', 'PURCHASING to create PO for inventory transaction', 5, 71),
            ('SHIP_TO_VENDOR', 'WAREHOUSE to ship item back to vendor', 6, 119),
            ('ISSUE_CREDIT_NOTE', 'ACCOUNTING to create negative Non Invoiced Accounts Payable (APN) transaction', 7, 73),
            ('REC_FROM_VENDOR', 'WAREHOUSE to receive part back from vendor', 8, 72),
            ('ISSUE_DEBIT_NOTE', 'ACCOUNTING to create positive Non Invoiced Accounts Payable (APN) transaction', 9, 73),
            ('VALIDATE_SCAR', 'QUALITY to validate and/or accept Supplier Corrective Action Request (SCAR) from vendor', 10, 70),
            ('CLOSED_RESOLVED', 'VWC Closed: Vendor actions done as per TLD request', 11, 52),
            ('CLOSED_LOW_VALUE', 'VWC Closed: Low value part, no action taken', 12, 52),
            ('CLOSED_VENDOR_REJECTED', 'VWC Closed: Vendor refused to honor the VWC', 13, 52),
            ('CLOSED_NOT_VENDOR_ISSUE', 'VWC Closed: Vendor not responsible for VWC', 14, 52);
        ");

        $this->addSql("INSERT IGNORE INTO vendor_warranty_claim_type (name, description) VALUES
            ('REPLACE', 'Send part back for a replacement one'),
            ('RETURN_FOR_CREDIT', 'Return back part and get credit'),
            ('REWORK', 'Charge back vendor for rework costs'),
            ('SCRAP', 'Charge back vendor for scrapping a part'),
            ('NON_COMPENSATING', 'Notify vendor of problem only');
        ");

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_VENDOR_WARRANTY_CLAIM_REOPEN")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_VENDOR_WARRANTY_CLAIM_QUALITY")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_REOPEN"
                          AND user_group.name IN ("SUPERUSER", "ROLE_SPM", "ROLE_MLM", "ROLE_COO", "ROLE_CPO")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_QUALITY"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM", "GG_QUALITY")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE non_conformity_parts DROP FOREIGN KEY FK_61A451DF8EA30491');
        $this->addSql('ALTER TABLE non_conformity_product DROP FOREIGN KEY FK_1875A7BF8EA30491');
        $this->addSql('ALTER TABLE non_conformity_files DROP FOREIGN KEY FK_C852FE218EA30491');
        $this->addSql('ALTER TABLE non_conformity_photo_files DROP FOREIGN KEY FK_C9220AC78EA30491');
        $this->addSql('ALTER TABLE vendor_warranty_claim_ncr DROP FOREIGN KEY FK_A7CCDDD58EA30491');
        $this->addSql('ALTER TABLE non_conformity DROP FOREIGN KEY FK_9726A49A7EC2F574');
        $this->addSql('ALTER TABLE supplier_corrective_action_request_files DROP FOREIGN KEY FK_6E5A5406C917A344');
        $this->addSql('ALTER TABLE supplier_corrective_action_request_main_files DROP FOREIGN KEY FK_2EFE067FC917A344');
        $this->addSql('ALTER TABLE supplier_corrective_action_requests_parts DROP FOREIGN KEY FK_10D93BFBC917A344');
        $this->addSql('ALTER TABLE vendor_warranty_claims DROP FOREIGN KEY FK_88ED6D08C917A344');
        $this->addSql('ALTER TABLE vendor_warranty_claims DROP FOREIGN KEY FK_88ED6D086BF700BD');
        $this->addSql('ALTER TABLE vendor_warranty_claims DROP FOREIGN KEY FK_88ED6D08C54C8C93');
        $this->addSql('ALTER TABLE vendor_warranty_claim_files DROP FOREIGN KEY FK_DD07B0BB75B4C7D4');
        $this->addSql('ALTER TABLE vendor_warranty_claim_ncr DROP FOREIGN KEY FK_A7CCDDD5BF396750');
        $this->addSql('ALTER TABLE vendor_warranty_claim_wc DROP FOREIGN KEY FK_3F916DA8BF396750');
        $this->addSql('ALTER TABLE vendor_warranty_claims_parts DROP FOREIGN KEY FK_A5BAA92E75B4C7D4');
        $this->addSql('DROP TABLE non_conformity_parts');
        $this->addSql('DROP TABLE non_conformity');
        $this->addSql('DROP TABLE non_conformity_product');
        $this->addSql('DROP TABLE non_conformity_files');
        $this->addSql('DROP TABLE non_conformity_photo_files');
        $this->addSql('DROP TABLE non_quality_costs');
        $this->addSql('DROP TABLE process');
        $this->addSql('DROP TABLE supplier_corrective_action_request');
        $this->addSql('DROP TABLE supplier_corrective_action_request_files');
        $this->addSql('DROP TABLE supplier_corrective_action_request_main_files');
        $this->addSql('DROP TABLE supplier_corrective_action_requests_parts');
        $this->addSql('DROP TABLE suppliers');
        $this->addSql('DROP TABLE vendor_warranty_claim_files');
        $this->addSql('DROP TABLE vendor_warranty_claim_ncr');
        $this->addSql('DROP TABLE vendor_warranty_claim_status');
        $this->addSql('DROP TABLE vendor_warranty_claim_type');
        $this->addSql('DROP TABLE vendor_warranty_claim_wc');
        $this->addSql('DROP TABLE vendor_warranty_claims');
        $this->addSql('DROP TABLE vendor_warranty_claims_parts');
        $this->addSql('ALTER TABLE parts CHANGE created_by_id created_by_id INT NOT NULL');
    }
}
