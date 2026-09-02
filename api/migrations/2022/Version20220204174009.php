<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220204174009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add specific features for evendors authorized app';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DMS_READ"), ("FEATURE_RFQ_READ"), ("FEATURE_ERP_PURCHASE_ORDERS_READ")');
    }

    public function down(Schema $schema): void
    {
    }
}
