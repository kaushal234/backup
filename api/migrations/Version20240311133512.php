<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240311133512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add "public" attribute in Criteria of Supplier Ranking';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplier_rankings_criterias ADD public TINYINT(1) NOT NULL DEFAULT 1');
        $this->addSql('UPDATE supplier_rankings_criterias SET public = 0 WHERE name = "Cost"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplier_rankings_criterias DROP public');
    }
}
