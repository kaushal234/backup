<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231003141154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add factory property on Sales Order Line';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales_order_lines ADD factory_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sales_order_lines ADD CONSTRAINT FK_8894E9AFC7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('CREATE INDEX IDX_8894E9AFC7AF27D2 ON sales_order_lines (factory_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales_order_lines DROP FOREIGN KEY FK_8894E9AFC7AF27D2');
        $this->addSql('DROP INDEX IDX_8894E9AFC7AF27D2 ON sales_order_lines');
        $this->addSql('ALTER TABLE sales_order_lines DROP factory_id');
    }
}
