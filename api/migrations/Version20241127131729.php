<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241127131729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove unused public properties.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_families DROP public');
        $this->addSql('ALTER TABLE product_types DROP public');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_families ADD public TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE product_types ADD public TINYINT(1) NOT NULL');
    }
}
