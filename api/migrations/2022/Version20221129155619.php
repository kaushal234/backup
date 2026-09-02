<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221129155619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add main file on WC Vendor Warranty Claim';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vendor_warranty_claim_main_files (id INT NOT NULL, vendor_warranty_claim_id INT DEFAULT NULL, INDEX IDX_34385C2D75B4C7D4 (vendor_warranty_claim_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vendor_warranty_claim_main_files ADD CONSTRAINT FK_34385C2D75B4C7D4 FOREIGN KEY (vendor_warranty_claim_id) REFERENCES vendor_warranty_claim_wc (id)');
        $this->addSql('ALTER TABLE vendor_warranty_claim_main_files ADD CONSTRAINT FK_34385C2DBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE vendor_warranty_claim_main_files');
    }
}
