<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260617122816 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'TOC - insert data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 23385,"toc.tags.ibs",NOW(),"toc")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 23385,"toc.tags.ihs",NOW(),"toc")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 23385,"toc.tags.link",NOW(),"toc")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 23385,"toc.tags.apu_off",NOW(),"toc")');
        $this->addSql('INSERT IGNORE INTO technician_on_call_tags (id) SELECT id FROM tags WHERE discr="toc"');

        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Troubleshooting", "Troubleshooting")');
        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Commissioning", "Commissioning")');
        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Service Bulletin", "Service Bulletin")');
        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Training", "Training")');
        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Maintenance", "Maintenance")');
        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Unit Upgrade", "Unit Upgrade")');
        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Info request", "Info request")');

        $this->addSql('INSERT IGNORE INTO technician_on_call_type (name, description) VALUES ( "toc.type.not_define_yet", "Not Defined Yet")');
        $this->addSql('INSERT IGNORE INTO technician_on_call_type (name, description) VALUES ( "toc.type.customer", "Customer (Payable Service)")');
        $this->addSql('INSERT IGNORE INTO technician_on_call_type (name, description) VALUES ( "toc.type.sso", "SSO (Sales Concession)")');
        $this->addSql('INSERT IGNORE INTO technician_on_call_type (name, description) VALUES ( "toc.type.factory", "Factory (Warranty, Compulsory SB…)")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TECHNICIAN_ON_CALL_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_TECHNICIAN_ON_CALL_EDIT"
                          AND user_group.name IN ("GG_SERVICE", "ROLE_CSM", "ROLE_EVP", "SUPERUSER", "ROLE_CSD")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TECHNICIAN_ON_CALL_OPEN_FACTORY_FLAG")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_TECHNICIAN_ON_CALL_OPEN_FACTORY_FLAG"
                          AND user_group.name IN ("GG_SERVICE", "ROLE_CSM", "ROLE_EVP", "SUPERUSER", "ROLE_CSD", "ROLE_AST")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TECHNICIAN_ON_CALL_CLOSE_FACTORY_FLAG")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_TECHNICIAN_ON_CALL_CLOSE_FACTORY_FLAG"
                          AND user_group.name IN ("GG_SERVICE", "ROLE_CSM", "ROLE_EVP", "SUPERUSER", "ROLE_CSD", "ROLE_AST", "ROLE_PSE", "ROLE_PSA", "ROLE_PSM", "ROLE_RME", "GG_ENG")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TECHNICIAN_ON_CALL_SUSPENDED")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_TECHNICIAN_ON_CALL_SUSPENDED"
                          AND user_group.name IN ("ROLE_CSM", "SUPERUSER", "ROLE_CSD")');

        $this->addSql("INSERT INTO technician_on_call_zone (name, delay) VALUES ('G', 48)");
        $this->addSql("INSERT INTO technician_on_call_zone (name, delay) VALUES ('B', 72)");
        $this->addSql("INSERT INTO technician_on_call_zone (name, delay) VALUES ('Y', 120)");
        $this->addSql("INSERT INTO technician_on_call_zone (name, delay) VALUES ('R', 168)");

        $this->insertFeatureGroup('FEATURE_TECHNICIAN_ON_CALL_SURVEY', ['ROLE_CSM', 'ROLE_CSD']);
        $this->insertFeatureGroup('FEATURE_TECHNICIAN_ON_CALL_SUSPENDED', ['ROLE_AST', 'ROLE_CSA'], false);
        $this->insertFeatureGroup('FEATURE_TECHNICIAN_ON_CALL_DELETE', ['ROLE_CSM', 'SUPERUSER']);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM tags WHERE discr="toc"');
    }
}
