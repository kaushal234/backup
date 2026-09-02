<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230821113820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE directory_location ADD erp_in_ln TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('UPDATE directory_location SET erp_in_ln = erp_in_baan');
        $this->addSql('ALTER TABLE directory_location DROP COLUMN erp_in_baan');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE directory_location ADD erp_in_baan TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('UPDATE directory_location SET erp_in_baan = erp_in_ln');
        $this->addSql('ALTER TABLE directory_location DROP erp_in_ln');
    }
}
