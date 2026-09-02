<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200819071449 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"ACU - Expansion devices (Electronic expansion valve)",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"ACU - Plate exchanger (Economizer)",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"ACU - Electrical heating bank",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"ASU and ACU - Water coil",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"ASU and ACU - Direct expansion coil",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Automation - Auxiliary PLC card",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Automation - Airflow sensor",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Bodywork - Panels",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Bodywork - Chassis Design",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Modems 3G - 4G",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Variable Frequency Drive (VFD)",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Bus bar",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Capacitor",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Contactor/Relay",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Electric panel",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Fan",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Gasket",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - PCB",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - PSU",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Solenoid",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Shunt",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Semiconductor Devices",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Circuit breakers",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Contactors and relays",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Power supply rectifier",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Junction blocks",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Cable trays",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Engine - GENSETs",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Engine - Terminal box",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Misc - Insulation",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Misc - Plastic casing",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Transmission - axles - Bearings",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Weldments - Chassis small size",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Air circuit components - Air ducting",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Air circuit components - Air filter",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Air circuit components - Air grid",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Air circuit components - Air flexible ducting (insulated or not)",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Air circuit components - Aircraft connector",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Air circuit components - Air valve",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Water circuit components - Water valve",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Water circuit components - Hydraulic control valve",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Water circuit components - Water pumps",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Water circuit components - Bronze wear rings",NOW(),"faq")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
