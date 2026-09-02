<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260113152343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add quote property in sales forecasts.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sales_forecasts ADD quote_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sales_forecasts ADD CONSTRAINT FK_56DB8EB3DB805178 FOREIGN KEY (quote_id) REFERENCES quote (id)');
        $this->addSql('CREATE INDEX IDX_56DB8EB3DB805178 ON sales_forecasts (quote_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sales_forecasts DROP FOREIGN KEY FK_56DB8EB3DB805178');
        $this->addSql('DROP INDEX IDX_56DB8EB3DB805178 ON sales_forecasts');
        $this->addSql('ALTER TABLE sales_forecasts DROP quote_id');
    }
}
