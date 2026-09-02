<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231004090804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ship_with_parts to sales_order_lines to see if certain parts should be included with';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales_order_lines ADD ship_with_parts TINYINT(1) NOT NULL, CHANGE status status VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales_order_lines DROP ship_with_parts, CHANGE status status VARCHAR(255) NOT NULL');
    }
}
