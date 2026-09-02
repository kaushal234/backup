<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251002120526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create new status for VWC';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT IGNORE INTO vendor_warranty_claim_status (name, description, position, group_id) VALUES
            ('CLOSED_DUPLICATE', 'VWC Closed because it is a duplicate', 15, 52);
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
