<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210929123225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create tables equipment_serials, equipment_serials_components and new feature';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_EQUIPMENT_SERIAL_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_EQUIPMENT_SERIAL_ADMIN"
                          AND user_group.name IN ("SUPERUSER", "PI_OPERATOR", "GG_ENG", "GG_SUPPORT")');

        $this->addSql('CREATE TABLE equipment_serial_components (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipment_serials (id INT AUTO_INCREMENT NOT NULL, equipment_record_id INT NOT NULL, component_id INT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, model VARCHAR(255) DEFAULT NULL, serial VARCHAR(255) DEFAULT NULL, brand VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, legacy_id INT NOT NULL, INDEX IDX_E2C3EAD29FC03375 (equipment_record_id), INDEX IDX_E2C3EAD2E2ABAFFF (component_id), INDEX IDX_E2C3EAD2DE12AB56 (created_by), INDEX IDX_E2C3EAD216FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE equipment_serials ADD CONSTRAINT FK_E2C3EAD29FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE equipment_serials ADD CONSTRAINT FK_E2C3EAD2E2ABAFFF FOREIGN KEY (component_id) REFERENCES equipment_serial_components (id)');
        $this->addSql('ALTER TABLE equipment_serials ADD CONSTRAINT FK_E2C3EAD2DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE equipment_serials ADD CONSTRAINT FK_E2C3EAD216FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_serials DROP FOREIGN KEY FK_E2C3EAD2E2ABAFFF');
        $this->addSql('DROP TABLE equipment_serial_components');
        $this->addSql('DROP TABLE equipment_serials');
    }
}
