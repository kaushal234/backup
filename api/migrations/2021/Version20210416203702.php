<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210416203702 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE couriers (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(512) DEFAULT NULL, parameter_name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX unique_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tracking_files (id INT NOT NULL, tracking_id INT DEFAULT NULL, INDEX IDX_ED6959F47D05ABBE (tracking_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trackings (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, location_id INT NOT NULL, courier_id INT DEFAULT NULL, created_at DATETIME NOT NULL, tracking_number VARCHAR(255) NOT NULL, packing_slip INT NOT NULL, document_type VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, legacy_id INT NOT NULL, INDEX IDX_FA7EF26B03A8386 (created_by_id), INDEX IDX_FA7EF2664D218E (location_id), INDEX IDX_FA7EF26E3D8151C (courier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tracking_files ADD CONSTRAINT FK_ED6959F47D05ABBE FOREIGN KEY (tracking_id) REFERENCES trackings (id)');
        $this->addSql('ALTER TABLE tracking_files ADD CONSTRAINT FK_ED6959F4BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE trackings ADD CONSTRAINT FK_FA7EF26B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trackings ADD CONSTRAINT FK_FA7EF2664D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE trackings ADD CONSTRAINT FK_FA7EF26E3D8151C FOREIGN KEY (courier_id) REFERENCES couriers (id)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_COURIER_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_COURIER_ADMIN"
                          AND user_group.name in ("SUPERUSER", "ROLE_SPM")'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TRACKING_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_TRACKING_WRITE"
                          AND user_group.name in ("SUPERUSER", "ROLE_SPM", "GG_PARTS", "GG_SERVICE", "GG_SUPPORT", "GG_SERVICE_AGENTS")'
        );

        $this->addSql("INSERT INTO couriers (name, url, parameter_name) VALUES ('UPS', 'http://wwwapps.ups.com/etracking/tracking.cgi?tracknums_displayed=5&TypeOfInquiryNumber=T&HTMLVersion=4.0', 'InquiryNumber1');");
        $this->addSql("INSERT INTO couriers (name, url, parameter_name) VALUES ('FED', 'http://www.fedex.com/tracking?ascend_header=1&clienttype=dotcom&cntry_code=us&language=english', 'tracknumbers');");
        $this->addSql("INSERT INTO couriers (name, url, parameter_name) VALUES ('DHL', 'http://www.dhl.com/cgi-bin/tracking.pl?TID=CP_ENG&FIRST_DB=', 'AWB');");
        $this->addSql("INSERT INTO couriers (name, url, parameter_name) VALUES ('TNT', 'http://www.tnt.com/webtracker/tracking.do?respCountry=&usrespLang=en&navigation=1&page=1&sourceID=1&sourceCountry=ww&plazaKey=&refs=&requesttype=GEN', 'cons');");
        $this->addSql("INSERT INTO couriers (name, url, parameter_name) VALUES ('OCS', 'http://www.ocs.co.jp/multitracking/tracking/template/MultiQuery.vm/action/MultiTracking?sURL=http%3A%2F%2Fwww.shipocs.com%2F&noshipmentURI=http%3A%2F%2Fwww.shipocs.com%2FtrackingSupport.asp&target=_self', 'CWBs');");
        $this->addSql("INSERT INTO couriers (name, url, parameter_name) VALUES ('EMS', 'http://www.ems.com.cn/chinese-main.jsp', 'tracking');");
        $this->addSql("INSERT INTO couriers (name, url, parameter_name) VALUES ('EXAPAQ', 'http://e-trace.ils-consult.fr/exa-webtrace/webtrace.aspx?cmd=SDG_MULTI_SEARCH', 'sdgnrs');");
        $this->addSql("INSERT INTO couriers (name, url, parameter_name) VALUES ('BAX', 'http://www.baxglobal.com/Tracking/TrackDetail.aspx?From=1&Isn=4541253&Org=&Dst=&Type=I&Code=0006&Mawb=60839892&SearchBy=H', 'SearchVal');");
        $this->addSql("INSERT INTO couriers (name) VALUES ('SUR');");
        $this->addSql("INSERT INTO couriers (name) VALUES ('SFE');");
        $this->addSql("INSERT INTO couriers (name) VALUES ('WAT');");
        $this->addSql("INSERT INTO couriers (name) VALUES ('GEFCO');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trackings DROP FOREIGN KEY FK_FA7EF26E3D8151C');
        $this->addSql('ALTER TABLE tracking_files DROP FOREIGN KEY FK_ED6959F47D05ABBE');
        $this->addSql('DROP TABLE couriers');
        $this->addSql('DROP TABLE tracking_files');
        $this->addSql('DROP TABLE trackings');
    }
}
