<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221020131712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Put back Baan supplier number from baan before recoding again';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE non_conformity SET supplier_number = baan_supplier_number WHERE factory_id IN (35, 45) AND created_at < "2022-10-11 00:00:00"');
        $this->addSql('UPDATE supplier_corrective_action_request SET supplier_number = baan_supplier_number WHERE factory_id IN (35, 45) AND created_at < "2022-10-11 00:00:00"');
        $this->addSql('UPDATE vendor_warranty_claims SET supplier_number = baan_supplier_number WHERE factory_id IN (35, 45) AND created_at < "2022-10-11 00:00:00"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
