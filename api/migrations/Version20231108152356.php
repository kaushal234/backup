<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231108152356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add supplier rankings entities and default datas.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE supplier_rankings (id INT AUTO_INCREMENT NOT NULL, supplier_country_id INT DEFAULT NULL, location_id INT NOT NULL, classification_id INT DEFAULT NULL, expertise_level_id INT DEFAULT NULL, last_review_by INT DEFAULT NULL, last_screening_by INT DEFAULT NULL, last_review_at DATETIME DEFAULT NULL, last_screening_at DATETIME DEFAULT NULL, supplier_number VARCHAR(255) NOT NULL, supplier_name VARCHAR(255) DEFAULT NULL, legacy_id INT NOT NULL, revenue DOUBLE PRECISION DEFAULT NULL, buyer_id INT DEFAULT NULL, is_valid TINYINT(1) NOT NULL, INDEX IDX_10D4CD6798DEF399 (supplier_country_id), INDEX IDX_10D4CD6764D218E (location_id), INDEX IDX_10D4CD672A86559F (classification_id), INDEX IDX_10D4CD674B9079AA (expertise_level_id), INDEX IDX_10D4CD67A913FC8F (last_review_by), INDEX IDX_10D4CD67F9F1E61B (last_screening_by), INDEX IDX_10D4CD676C755722 (buyer_id), UNIQUE INDEX supplier_number_location (supplier_number, location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_rankings_classifications (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, is_supplier_approved TINYINT(1) NOT NULL, workflow_level INT DEFAULT NULL, color VARCHAR(6) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_rankings_classification_targets (original_id INT NOT NULL, target_id INT NOT NULL, INDEX IDX_8D5714FF108B7592 (original_id), INDEX IDX_8D5714FF158E0B66 (target_id), PRIMARY KEY(original_id, target_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_rankings_criterias (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, min_turnover INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_rankings_expertise_levels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_rankings_file_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_rankings_files (id INT NOT NULL, category_id INT NOT NULL, supplier_ranking_id INT DEFAULT NULL, expired_at DATETIME DEFAULT NULL, INDEX IDX_3D6932F312469DE2 (category_id), INDEX IDX_3D6932F341078B6D (supplier_ranking_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_rankings_notations (supplier_ranking_id INT NOT NULL, criteria_id INT NOT NULL, notation SMALLINT DEFAULT NULL, INDEX IDX_49CF7E5F41078B6D (supplier_ranking_id), INDEX IDX_49CF7E5F990BEA15 (criteria_id), PRIMARY KEY(supplier_ranking_id, criteria_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_rankings_periodicities (expertise_level_id INT NOT NULL, classification_id INT NOT NULL, months SMALLINT NOT NULL, INDEX IDX_C2C067014B9079AA (expertise_level_id), INDEX IDX_C2C067012A86559F (classification_id), PRIMARY KEY(expertise_level_id, classification_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_rankings_thresholds (id INT AUTO_INCREMENT NOT NULL, classification_id INT NOT NULL, expertise_level_id INT NOT NULL, min_criterias SMALLINT DEFAULT 1 NOT NULL, description LONGTEXT DEFAULT NULL, name VARCHAR(100) NOT NULL, show_in_graph TINYINT(1) NOT NULL, INDEX IDX_A03C549C2A86559F (classification_id), INDEX IDX_A03C549C4B9079AA (expertise_level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier_rankings_thresholds_criterias (threshold_id INT NOT NULL, criteria_id INT NOT NULL, rank_limit SMALLINT NOT NULL, INDEX IDX_AD7B2F7A40645593 (threshold_id), INDEX IDX_AD7B2F7A990BEA15 (criteria_id), PRIMARY KEY(threshold_id, criteria_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supplier_rankings ADD CONSTRAINT FK_10D4CD6798DEF399 FOREIGN KEY (supplier_country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE supplier_rankings ADD CONSTRAINT FK_10D4CD6764D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE supplier_rankings ADD CONSTRAINT FK_10D4CD672A86559F FOREIGN KEY (classification_id) REFERENCES supplier_rankings_classifications (id)');
        $this->addSql('ALTER TABLE supplier_rankings ADD CONSTRAINT FK_10D4CD674B9079AA FOREIGN KEY (expertise_level_id) REFERENCES supplier_rankings_expertise_levels (id)');
        $this->addSql('ALTER TABLE supplier_rankings ADD CONSTRAINT FK_10D4CD67A913FC8F FOREIGN KEY (last_review_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE supplier_rankings ADD CONSTRAINT FK_10D4CD67F9F1E61B FOREIGN KEY (last_screening_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE supplier_rankings ADD CONSTRAINT FK_10D4CD676C755722 FOREIGN KEY (buyer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE supplier_rankings_classification_targets ADD CONSTRAINT FK_8D5714FF108B7592 FOREIGN KEY (original_id) REFERENCES supplier_rankings_classifications (id)');
        $this->addSql('ALTER TABLE supplier_rankings_classification_targets ADD CONSTRAINT FK_8D5714FF158E0B66 FOREIGN KEY (target_id) REFERENCES supplier_rankings_classifications (id)');
        $this->addSql('ALTER TABLE supplier_rankings_files ADD CONSTRAINT FK_3D6932F312469DE2 FOREIGN KEY (category_id) REFERENCES supplier_rankings_file_categories (id)');
        $this->addSql('ALTER TABLE supplier_rankings_files ADD CONSTRAINT FK_3D6932F341078B6D FOREIGN KEY (supplier_ranking_id) REFERENCES supplier_rankings (id)');
        $this->addSql('ALTER TABLE supplier_rankings_files ADD CONSTRAINT FK_3D6932F3BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supplier_rankings_notations ADD CONSTRAINT FK_49CF7E5F41078B6D FOREIGN KEY (supplier_ranking_id) REFERENCES supplier_rankings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supplier_rankings_notations ADD CONSTRAINT FK_49CF7E5F990BEA15 FOREIGN KEY (criteria_id) REFERENCES supplier_rankings_criterias (id)');
        $this->addSql('ALTER TABLE supplier_rankings_periodicities ADD CONSTRAINT FK_C2C067014B9079AA FOREIGN KEY (expertise_level_id) REFERENCES supplier_rankings_expertise_levels (id)');
        $this->addSql('ALTER TABLE supplier_rankings_periodicities ADD CONSTRAINT FK_C2C067012A86559F FOREIGN KEY (classification_id) REFERENCES supplier_rankings_classifications (id)');
        $this->addSql('ALTER TABLE supplier_rankings_thresholds ADD CONSTRAINT FK_A03C549C2A86559F FOREIGN KEY (classification_id) REFERENCES supplier_rankings_classifications (id)');
        $this->addSql('ALTER TABLE supplier_rankings_thresholds ADD CONSTRAINT FK_A03C549C4B9079AA FOREIGN KEY (expertise_level_id) REFERENCES supplier_rankings_expertise_levels (id)');
        $this->addSql('ALTER TABLE supplier_rankings_thresholds_criterias ADD CONSTRAINT FK_AD7B2F7A40645593 FOREIGN KEY (threshold_id) REFERENCES supplier_rankings_thresholds (id)');
        $this->addSql('ALTER TABLE supplier_rankings_thresholds_criterias ADD CONSTRAINT FK_AD7B2F7A990BEA15 FOREIGN KEY (criteria_id) REFERENCES supplier_rankings_criterias (id)');

        // Classifications default datas
        $this->addSql("INSERT INTO supplier_rankings_classifications (id, name, description, is_supplier_approved, workflow_level, color)
                           VALUES (1, 'Unrestricted', 'Supplier can be used W/O restrictions.

Working with this type of provider is recommended.', 1, null, '399a4b'),
        (2, 'Monitored', 'Means the current supplier can be used but is expected to improve its performance on some criteria.

Or for new supplier, the next step is to be integrated in the “Unrestricted list”.', 1, 2, '1A3AA5'),
        (3, 'Restricted', 'These providers do not currently meet the assessment criteria; an action plan is under way to solve problems.

Collaboration is possible only within the limits defined by the action plan, also called improvement plan.

It means the supplier must be used on new components with extreme care as action plan are implemented to correct their assessment deviations.', 1, 1, 'f8ac59'),
        (4, 'Locked', 'Suppliers to be eliminated.

Alvest or TLD doesn’t want to work with these suppliers any longer; an action plan (Phase Out) associated with a planning is underway, collaboration is only allowed within the limits defined by the exit plan.', 0, 0, 'ED5565'),
        (5, 'Suppressed', 'Deleted or rejected suppliers.

They have been removed by our purchasing department; working with these providers is prohibited.

These vendors want to work with Alvest or TLD but they don’t meet our criteria of evaluation.

Working with this type of provider is prohibited.', 0, null, 'ED5565')");
        $this->addSql('INSERT INTO supplier_rankings_classification_targets (original_id, target_id)
                           VALUES (1, 2),
                                  (2, 1),
                                  (4, 5),
                                  (5, 4)');

        // Criterias default datas
        $this->addSql("INSERT INTO supplier_rankings_criterias (id, name, min_turnover)
                           VALUES (1, 'Cost', 0),
                                  (2, 'Quality', 0),
                                  (3, 'Logistic', 0),
                                  (4, 'Communication, transparency and responsiveness', 0),
                                  (5, 'Innovation & Partnership', 0),
                                  (6, 'Product and field support', 0),
                                  (7, 'Environmental, Social, Governance', 200000),
                                  (8, 'Anti-corruption', 100000),
                                  (9, 'Cybersecurity', 0)");

        // Expertise levels default datas
        $this->addSql("INSERT INTO supplier_rankings_expertise_levels (id, name, description)
VALUES (1, 'OCM', 'Original Component Supplier which is the highest level of expertise owned by a TLD Supplier. An OCM owns component operational, functional and engineering expertise as well as manufacturing expertise.

Example: engine, axle or compressor supplier.'),
        (2, 'Specialist', 'Supplier may not fully master the TLD component operational expertise but has a full expertise on the technologies used in order to develop a component or function defined by TLD engineers.

Example: radiator supplier working from heat exchange specification, cylinder, bolt and nuts or hose supplier.'),
        (3, 'Fabricator', 'Supplier manufacturers under TLD drawings and that only owns expertise linked to fabrication (Welding, machining).

Example: weldment or harness supplier.')");

        // Periodicities default datas
        $this->addSql('INSERT INTO supplier_rankings_periodicities (expertise_level_id, classification_id, months)
                           VALUES (1, 1, 36),
                                  (1, 2, 24),
                                  (1, 3, 12),
                                  (2, 1, 12),
                                  (2, 2, 12),
                                  (2, 3, 6),
                                  (3, 1, 9),
                                  (3, 2, 6),
                                  (3, 3, 3)');

        // File categories default datas
        $this->addSql("INSERT INTO supplier_rankings_file_categories (id, name)
                           VALUES (1, 'Qualification'),
                                  (2, 'Contracts'),
                                  (3, 'Prices'),
                                  (4, 'Minutes of meeting'),
                                  (5, 'Code Ethic'),
                                  (6, 'ISO9001'),
                                  (7, 'ISO14001'),
                                  (8, 'ESG'),
                                  (9, 'Others')");

        // Thresholds
        $this->addSql("INSERT INTO supplier_rankings_thresholds (id, name, classification_id, expertise_level_id, min_criterias, description, show_in_graph)
                           VALUES (1, 'Locking limit', 4, 1, 1, 'One criteria below rank 1 update the supplier ranking to Locked classification.', 1),
                                  (2, 'Locking limit', 4, 2, 1, 'One criteria below rank 1 update the supplier ranking to Locked classification.', 1),
                                  (3, 'Locking limit', 4, 3, 1, 'One criteria below rank 1 update the supplier ranking to Locked classification.', 1),
                                  (4, 'Restricted limit', 3, 1, 1, 'Quality or Anti-corruption below 3 update the supplier ranking to Restricted classification.', 1),
                                  (5, 'Restricted limit', 3, 2, 1, 'Quality or Anti-corruption below 3 update the supplier ranking to Restricted classification.', 1),
                                  (6, 'Restricted limit', 3, 3, 1, 'Quality or Anti-corruption below 3 update the supplier ranking to Restricted classification.', 1),
                                  (7, 'Restricted limit', 3, 1, 3, 'Specific rule to update supplier ranking to Restricted depending on 3 criterias without Quality and Anti-corruption.', 1),
                                  (8, 'Restricted limit', 3, 2, 3, 'Specific rule to update supplier ranking to Restricted depending on 3 criterias without Quality and Anti-corruption.', 1),
                                  (9, 'Restricted limit', 3, 3, 3, 'Specific rule to update supplier ranking to Restricted depending on 3 criterias without Quality and Anti-corruption.', 1),
                                  (10, 'Default monitored', 2, 1, 1, 'Main rule to update supplier ranking to Monitored by default.', 0),
                                  (11, 'Default monitored', 2, 2, 1, 'Main rule to update supplier ranking to Monitored by default.', 0),
                                  (12, 'Default monitored', 2, 3, 1, 'Main rule to update supplier ranking to Monitored by default.', 0)");
        $this->addSql('INSERT INTO supplier_rankings_thresholds_criterias (threshold_id, criteria_id, rank_limit)
                           VALUES (1, 1, 1),
                                  (1, 2, 1),
                                  (1, 3, 1),
                                  (1, 4, 1),
                                  (1, 5, 1),
                                  (1, 6, 1),
                                  (1, 7, 1),
                                  (1, 8, 1),
                                  (1, 9, 1),
                                  (2, 1, 1),
                                  (2, 2, 1),
                                  (2, 3, 1),
                                  (2, 4, 1),
                                  (2, 5, 1),
                                  (2, 6, 1),
                                  (2, 7, 1),
                                  (2, 8, 1),
                                  (2, 9, 1),
                                  (3, 1, 1),
                                  (3, 2, 1),
                                  (3, 3, 1),
                                  (3, 4, 1),
                                  (3, 5, 1),
                                  (3, 6, 1),
                                  (3, 7, 1),
                                  (3, 8, 1),
                                  (3, 9, 1),
                                  (4, 2, 3),
                                  (4, 8, 3),
                                  (5, 2, 3),
                                  (5, 8, 3),
                                  (6, 2, 3),
                                  (6, 8, 3),
                                  (7, 1, 3),
                                  (7, 3, 3),
                                  (7, 4, 3),
                                  (7, 5, 3),
                                  (7, 6, 4),
                                  (7, 7, 3),
                                  (7, 9, 3),
                                  (8, 1, 3),
                                  (8, 3, 3),
                                  (8, 4, 3),
                                  (8, 5, 4),
                                  (8, 6, 3),
                                  (8, 7, 3),
                                  (8, 9, 3),
                                  (9, 1, 3),
                                  (9, 3, 3),
                                  (9, 4, 3),
                                  (9, 5, 3),
                                  (9, 6, 3),
                                  (9, 7, 3),
                                  (9, 9, 3),
                                  (10, 1, 5),
                                  (10, 2, 5),
                                  (10, 3, 5),
                                  (10, 4, 5),
                                  (10, 5, 5),
                                  (10, 6, 5),
                                  (10, 7, 5),
                                  (10, 8, 5),
                                  (10, 9, 5),
                                  (11, 1, 5),
                                  (11, 2, 5),
                                  (11, 3, 5),
                                  (11, 4, 5),
                                  (11, 5, 5),
                                  (11, 6, 5),
                                  (11, 7, 5),
                                  (11, 8, 5),
                                  (11, 9, 5),
                                  (12, 1, 5),
                                  (12, 2, 5),
                                  (12, 3, 5),
                                  (12, 4, 5),
                                  (12, 5, 5),
                                  (12, 6, 5),
                                  (12, 7, 5),
                                  (12, 8, 5),
                                  (12, 9, 5)');

        $this->addSql("INSERT IGNORE INTO feature (name)
                           VALUES ('FEATURE_SUPPLIER_RANKING_READ'),
                                  ('FEATURE_SUPPLIER_RANKING_READ_ALL'),
                                  ('FEATURE_SUPPLIER_RANKING_UPDATE'),
                                  ('FEATURE_SUPPLIER_RANKING_CLASSIFICATION_CREATE'),
                                  ('FEATURE_SUPPLIER_RANKING_CLASSIFICATION_READ'),
                                  ('FEATURE_SUPPLIER_RANKING_CLASSIFICATION_UPDATE'),
                                  ('FEATURE_SUPPLIER_RANKING_CLASSIFICATION_DELETE'),
                                  ('FEATURE_SUPPLIER_RANKING_EXPERTISE_LEVEL_CREATE'),
                                  ('FEATURE_SUPPLIER_RANKING_EXPERTISE_LEVEL_READ'),
                                  ('FEATURE_SUPPLIER_RANKING_EXPERTISE_LEVEL_UPDATE'),
                                  ('FEATURE_SUPPLIER_RANKING_EXPERTISE_LEVEL_DELETE'),
                                  ('FEATURE_SUPPLIER_RANKING_CRITERIA_CREATE'),
                                  ('FEATURE_SUPPLIER_RANKING_CRITERIA_READ'),
                                  ('FEATURE_SUPPLIER_RANKING_CRITERIA_UPDATE'),
                                  ('FEATURE_SUPPLIER_RANKING_CRITERIA_DELETE'),
                                  ('FEATURE_SUPPLIER_RANKING_THRESHOLD_CREATE'),
                                  ('FEATURE_SUPPLIER_RANKING_THRESHOLD_READ'),
                                  ('FEATURE_SUPPLIER_RANKING_THRESHOLD_UPDATE'),
                                  ('FEATURE_SUPPLIER_RANKING_THRESHOLD_DELETE'),
                                  ('FEATURE_SUPPLIER_RANKING_PERIODICITY_CREATE'),
                                  ('FEATURE_SUPPLIER_RANKING_PERIODICITY_READ'),
                                  ('FEATURE_SUPPLIER_RANKING_PERIODICITY_UPDATE'),
                                  ('FEATURE_SUPPLIER_RANKING_PERIODICITY_DELETE')");

        $this->insertFeatureGroup('FEATURE_SUPPLIER_RANKING_READ', ['SUPERUSER', 'ROLE_CPO', 'ROLE_BYR', 'ROLE_MLM']);
        $this->insertFeatureGroup('FEATURE_SUPPLIER_RANKING_READ_ALL', ['SUPERUSER', 'ROLE_CPO']);
        $this->insertFeatureGroup('FEATURE_SUPPLIER_RANKING_UPDATE', ['SUPERUSER', 'ROLE_CPO']);
        $this->insertFeatureGroup('FEATURE_SUPPLIER_RANKING_CLASSIFICATION_READ', ['SUPERUSER', 'ROLE_CPO', 'ROLE_BYR', 'ROLE_MLM']);
        $this->insertFeatureGroup('FEATURE_SUPPLIER_RANKING_EXPERTISE_LEVEL_READ', ['SUPERUSER', 'ROLE_CPO', 'ROLE_BYR', 'ROLE_MLM']);
        $this->insertFeatureGroup('FEATURE_SUPPLIER_RANKING_CRITERIA_READ', ['SUPERUSER', 'ROLE_CPO', 'ROLE_BYR', 'ROLE_MLM']);
    }

    /**
     * Link feature to a group.
     */
    public function insertFeatureGroup(string $feature, array $groups)
    {
        foreach ($groups as $group) {
            $this->addSql("INSERT IGNORE INTO feature_group (group_id, feature_id)
                           SELECT user_group.id, feature.id
                           FROM user_group, feature
                           WHERE feature.name = '$feature'
                              AND user_group.name IN ('$group')");
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplier_rankings DROP FOREIGN KEY FK_10D4CD6798DEF399');
        $this->addSql('ALTER TABLE supplier_rankings DROP FOREIGN KEY FK_10D4CD6764D218E');
        $this->addSql('ALTER TABLE supplier_rankings DROP FOREIGN KEY FK_10D4CD672A86559F');
        $this->addSql('ALTER TABLE supplier_rankings DROP FOREIGN KEY FK_10D4CD674B9079AA');
        $this->addSql('ALTER TABLE supplier_rankings DROP FOREIGN KEY FK_10D4CD67A913FC8F');
        $this->addSql('ALTER TABLE supplier_rankings DROP FOREIGN KEY FK_10D4CD67F9F1E61B');
        $this->addSql('ALTER TABLE supplier_rankings DROP FOREIGN KEY FK_10D4CD676C755722');
        $this->addSql('ALTER TABLE supplier_rankings_classification_targets DROP FOREIGN KEY FK_8D5714FF108B7592');
        $this->addSql('ALTER TABLE supplier_rankings_classification_targets DROP FOREIGN KEY FK_8D5714FF158E0B66');
        $this->addSql('ALTER TABLE supplier_rankings_files DROP FOREIGN KEY FK_3D6932F312469DE2');
        $this->addSql('ALTER TABLE supplier_rankings_files DROP FOREIGN KEY FK_3D6932F341078B6D');
        $this->addSql('ALTER TABLE supplier_rankings_files DROP FOREIGN KEY FK_3D6932F3BF396750');
        $this->addSql('ALTER TABLE supplier_rankings_notations DROP FOREIGN KEY FK_49CF7E5F41078B6D');
        $this->addSql('ALTER TABLE supplier_rankings_notations DROP FOREIGN KEY FK_49CF7E5F990BEA15');
        $this->addSql('ALTER TABLE supplier_rankings_periodicities DROP FOREIGN KEY FK_C2C067014B9079AA');
        $this->addSql('ALTER TABLE supplier_rankings_periodicities DROP FOREIGN KEY FK_C2C067012A86559F');
        $this->addSql('ALTER TABLE supplier_rankings_thresholds DROP FOREIGN KEY FK_A03C549C2A86559F');
        $this->addSql('ALTER TABLE supplier_rankings_thresholds DROP FOREIGN KEY FK_A03C549C4B9079AA');
        $this->addSql('ALTER TABLE supplier_rankings_thresholds_criterias DROP FOREIGN KEY FK_AD7B2F7A40645593');
        $this->addSql('ALTER TABLE supplier_rankings_thresholds_criterias DROP FOREIGN KEY FK_AD7B2F7A990BEA15');
        $this->addSql('DROP TABLE supplier_rankings');
        $this->addSql('DROP TABLE supplier_rankings_classifications');
        $this->addSql('DROP TABLE supplier_rankings_classification_targets');
        $this->addSql('DROP TABLE supplier_rankings_criterias');
        $this->addSql('DROP TABLE supplier_rankings_expertise_levels');
        $this->addSql('DROP TABLE supplier_rankings_file_categories');
        $this->addSql('DROP TABLE supplier_rankings_files');
        $this->addSql('DROP TABLE supplier_rankings_notations');
        $this->addSql('DROP TABLE supplier_rankings_periodicities');
        $this->addSql('DROP TABLE supplier_rankings_thresholds');
        $this->addSql('DROP TABLE supplier_rankings_thresholds_criterias');
    }
}
