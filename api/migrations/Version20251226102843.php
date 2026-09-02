<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251226102843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add location property to non_conformity and non_quality_cost table and migrates data in location from factory for both.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE non_conformity CHANGE factory_id factory_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE non_conformity ADD location_id INT DEFAULT NULL');
        $this->addSql('UPDATE non_conformity SET location_id = factory_id WHERE factory_id IS NOT NULL');
        $this->addSql('ALTER TABLE non_conformity ADD CONSTRAINT FK_9726A49A64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('CREATE INDEX IDX_9726A49A64D218E ON non_conformity (location_id)');

        $this->addSql('ALTER TABLE non_quality_costs ADD location_id INT DEFAULT NULL');
        $this->addSql('UPDATE non_quality_costs SET location_id = factory_id');
        $this->addSql('ALTER TABLE non_quality_costs CHANGE location_id location_id INT NOT NULL, CHANGE factory_id factory_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE non_quality_costs ADD CONSTRAINT FK_26904F7C64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('CREATE INDEX IDX_26904F7C64D218E ON non_quality_costs (location_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_non_quality_cost_per_location ON non_quality_costs (location_id, default_costs)');
        $this->addSql('DROP INDEX unique_non_quality_cost_per_factory ON non_quality_costs');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE non_conformity CHANGE factory_id factory_id INT NOT NULL');
        $this->addSql('ALTER TABLE non_conformity DROP FOREIGN KEY FK_9726A49A64D218E');
        $this->addSql('DROP INDEX IDX_9726A49A64D218E ON non_conformity');
        $this->addSql('ALTER TABLE non_conformity DROP location_id');
        $this->addSql('ALTER TABLE non_quality_costs DROP FOREIGN KEY FK_26904F7C64D218E');
        $this->addSql('DROP INDEX IDX_26904F7C64D218E ON non_quality_costs');
        $this->addSql('DROP INDEX unique_non_quality_cost_per_location ON non_quality_costs');
        $this->addSql('UPDATE non_quality_costs SET factory_id = location_id WHERE factory_id IS NULL');
        $this->addSql('ALTER TABLE non_quality_costs CHANGE factory_id factory_id INT NOT NULL');
        $this->addSql('ALTER TABLE non_quality_costs DROP location_id');
        $this->addSql('CREATE UNIQUE INDEX unique_non_quality_cost_per_factory ON non_quality_costs (factory_id, default_costs)');
    }
}
