<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191007124303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE manufacturing_margin (id INT AUTO_INCREMENT NOT NULL, equipment_record_id INT DEFAULT NULL, currency_id INT NOT NULL, exported_at DATETIME NOT NULL, option_configuration_parameter_hours DOUBLE PRECISION DEFAULT NULL, actual_hours DOUBLE PRECISION NOT NULL, standard_hours DOUBLE PRECISION NOT NULL, standard_labour_cost DOUBLE PRECISION NOT NULL, actual_labour_cost DOUBLE PRECISION NOT NULL, standard_material_cost DOUBLE PRECISION NOT NULL, actual_material_cost DOUBLE PRECISION NOT NULL, standard_other_material_cost DOUBLE PRECISION NOT NULL, actual_other_material_cost DOUBLE PRECISION NOT NULL, standard_other_direct_cost DOUBLE PRECISION NOT NULL, actual_other_direct_cost DOUBLE PRECISION NOT NULL, factory_revenue DOUBLE PRECISION NOT NULL, comment LONGTEXT DEFAULT NULL, legacy_id INT NOT NULL, INDEX IDX_71127A2B38248176 (currency_id), UNIQUE INDEX unique_equipment_record (equipment_record_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE manufacturing_margin ADD CONSTRAINT FK_71127A2B9FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE manufacturing_margin ADD CONSTRAINT FK_71127A2B38248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MANUFACTURING_MARGIN_VIEW_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MANUFACTURING_MARGIN_VIEW_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MANUFACTURING_MARGIN_DELETE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_MANUFACTURING_MARGIN_VIEW_CREATE"
			AND user_group.name in ( "ROLE_CFO", "ROLE_FC", "GG_ADMIN", "SUPERUSER" )'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_MANUFACTURING_MARGIN_VIEW_EDIT"
			AND user_group.name in ( "ROLE_CFO", "GG_ADMIN", "SUPERUSER" )'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_MANUFACTURING_MARGIN_DELETE"
			AND user_group.name in ( "SUPERUSER" )'
        );

        $this->addSql('ALTER TABLE equipment_records ADD manufacturer_location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipment_records ADD CONSTRAINT FK_EAE697A969A35B5 FOREIGN KEY (manufacturer_location_id) REFERENCES directory_location (id)');
        $this->addSql('CREATE INDEX IDX_EAE697A969A35B5 ON equipment_records (manufacturer_location_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE manufacturing_margin');
    }
}
