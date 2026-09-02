<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201109100518 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_families ADD public_on_tld TINYINT(1) NOT NULL, ADD public_on_aerospecialties TINYINT(1) NOT NULL, ADD public_on_sas TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE product_types ADD public_on_tld TINYINT(1) NOT NULL, ADD public_on_aerospecialties TINYINT(1) NOT NULL, ADD public_on_sas TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_families DROP public_on_tld, DROP public_on_aerospecialties, DROP public_on_sas');
        $this->addSql('ALTER TABLE product_types DROP public_on_tld, DROP public_on_aerospecialties, DROP public_on_sas');
    }
}
