<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305101219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add location in VWC and copy factory data in it';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vendor_warranty_claims ADD location_id INT NOT NULL, CHANGE factory_id factory_id INT DEFAULT NULL');
        $this->addSql('UPDATE vendor_warranty_claims SET location_id = factory_id');
        $this->addSql('ALTER TABLE vendor_warranty_claims MODIFY location_id INT NOT NULL');
        $this->addSql('ALTER TABLE vendor_warranty_claims ADD CONSTRAINT FK_88ED6D0864D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('CREATE INDEX IDX_88ED6D0864D218E ON vendor_warranty_claims (location_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vendor_warranty_claims DROP FOREIGN KEY FK_88ED6D0864D218E');
        $this->addSql('DROP INDEX IDX_88ED6D0864D218E ON vendor_warranty_claims');
        $this->addSql('ALTER TABLE vendor_warranty_claims DROP location_id, CHANGE factory_id factory_id INT NOT NULL');
    }
}
