<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180808000000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - Cluster",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - Steering column",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - Commodo ",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - swiches, joystick",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - Brake pedal",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - Accelerator pedal",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - Dead mann pedal",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - Seat",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - Air Conditionning",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - Heating",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - Front lights",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - Rear lights",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - Latches",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cab - Wipers blade and motors",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Weldments - Chassis medium size",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Weldments - Chassis heavy size",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Weldments - Sissors",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Weldments - Weldments",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Transmission - axles - Steering Axle",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Transmission - axles - Driving Axle",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Transmission - axles - Powershift  transmission",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Transmission - axles - Gearbox",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Transmission - axles - Transfer box",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Transmission - axles - Drive shafts",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Engine - Engine",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Engine - Air filter",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Engine - Exhaust line",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Engine - Air ducts",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Engine - Water pipe",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Engine - AD blue line",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Engine - Engine radiator",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Engine - Fan",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Engine - Shock absorbers",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Ground connection - Solid tires",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Ground connection - Pneumatic tires",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Ground connection - Rims",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Ground connection - Leaf springs",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Ground connection - Suspension",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Tanks - Fuel tank",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Tanks - Hyd. Tank",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Tanks - AD blue tank",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Tanks - Tank sensor (level)",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Tanks - Tank breather",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Filters",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Clocging indicator",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Strainer",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Radiator",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Variable displacement hyd. pump",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Fix displacement hyd. pump",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Variable displacement motor",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Fix displacement hyd. motor",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Hyd. valves",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Hyd. Proportional valve",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Hyd. Proportional valve CAN BUS",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Hyd. Rigid pipe",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Hyd. Hoses",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Fittings and mechanical valves",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Hyd. Manifold",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Cylinders included displacement sensor",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Hydraulique - Cylinders",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - 400 Hz generators",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Harness",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Connectors",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Can Bus connectors",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - EMI shilding tube",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Heatshring tube",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Splices",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Plugs (battery plug, GPU plug…)",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Bulbs and lamps",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Lead acid traction battery ",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Li traction battery",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Engine start  battery",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Electric box",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Electric motor AC",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Main inverter (traction, hygh power)",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Auxillary inverter",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Generator",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Heatsink",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Transformer",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Electricity and Elec. drive line - Electric cables and plugs",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Automation - PLC",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Automation - Pressure sensor",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Automation - Displacment sensor",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Automation - Temperature sensor",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Automation - Proxility switches",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Automation - Displays",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Automation - Electronic card ",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Automation - Camera",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Automation - Chock sensor and accelerometer",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Machining - Pin",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Machining - Steel rollers",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Machining - Rubber roller",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Machining - Castings",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Machining - Misc. Machining parts",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cutting and forming - Laser cutting",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cutting and forming - Punching",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Cutting and forming - Bended parts",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Foundry - Ballast",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Foundry - carters adaptation moteur BV",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"ASU and ACU - Compressor",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"ASU and ACU - Condensor",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"ASU and ACU - Evaporator",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"ASU and ACU - Duct",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Misc - casters",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Misc - boggey wheels",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Misc - printed label- technical marking - logos",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Misc - swelling ring",NOW(),"faq")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Misc - nuts and washers",NOW(),"faq")');

        $this->addSql('INSERT IGNORE INTO first_article_qualifications_tags (id) SELECT id FROM tags WHERE discr="faq"');
    }

    public function down(Schema $schema): void
    {
        // TODO: Implement down() method.
    }
}
