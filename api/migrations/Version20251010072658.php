<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251010072658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create product_families_manufacturing_factories Join Table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_families_manufacturing_factories (product_family_id INT NOT NULL, location_id INT NOT NULL, INDEX IDX_8516A963ADFEE0E7 (product_family_id), INDEX IDX_8516A96364D218E (location_id), PRIMARY KEY(product_family_id, location_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_families_manufacturing_factories ADD CONSTRAINT FK_8516A963ADFEE0E7 FOREIGN KEY (product_family_id) REFERENCES product_families (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_families_manufacturing_factories ADD CONSTRAINT FK_8516A96364D218E FOREIGN KEY (location_id) REFERENCES directory_location (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_families_manufacturing_factories DROP FOREIGN KEY FK_8516A963ADFEE0E7');
        $this->addSql('ALTER TABLE product_families_manufacturing_factories DROP FOREIGN KEY FK_8516A96364D218E');
        $this->addSql('DROP TABLE product_families_manufacturing_factories');
    }
}
