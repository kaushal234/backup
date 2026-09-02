<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260106151045 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new group for VWC status ISSUE_DEBIT_NOTE.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO user_group(name, description) VALUES('SEQ_VWC.AR', 'A/R personnel involved in VWC process')");
        $this->addSql("UPDATE vendor_warranty_claim_status SET group_id=(SELECT id FROM user_group WHERE name='SEQ_VWC.AR') WHERE name='ISSUE_DEBIT_NOTE'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE vendor_warranty_claim_status SET group_id=(SELECT id FROM user_group WHERE name='SEQ_VWC.AP') WHERE name='ISSUE_DEBIT_NOTE'");
        $this->addSql("DELETE FROM user_group WHERE name='SEQ_VWC.AR'");
    }
}
