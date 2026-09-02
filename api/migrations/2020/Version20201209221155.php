<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201209221155 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE eparts_area_routings (id INT AUTO_INCREMENT NOT NULL, eparts_order_routing_configuration_id INT NOT NULL, area VARCHAR(3) NOT NULL, routing VARCHAR(3) NOT NULL, INDEX IDX_2CD53E6688DEC631 (eparts_order_routing_configuration_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE eparts_order_routing_configurations (id INT AUTO_INCREMENT NOT NULL, sph_id INT NOT NULL, default_routing VARCHAR(3) NOT NULL, INDEX IDX_1D4F5A45A234FDCD (sph_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE eparts_area_routings ADD CONSTRAINT FK_2CD53E6688DEC631 FOREIGN KEY (eparts_order_routing_configuration_id) REFERENCES eparts_order_routing_configurations (id)');
        $this->addSql('ALTER TABLE eparts_order_routing_configurations ADD CONSTRAINT FK_1D4F5A45A234FDCD FOREIGN KEY (sph_id) REFERENCES directory_location (id)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_EPARTS_CONFIGURATION_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_EPARTS_CONFIGURATION_ADMIN"
          AND user_group.name = "SUPERUSER"'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE eparts_area_routings DROP FOREIGN KEY FK_2CD53E6688DEC631');
        $this->addSql('DROP TABLE eparts_area_routings');
        $this->addSql('DROP TABLE eparts_order_routing_configurations');
    }
}
