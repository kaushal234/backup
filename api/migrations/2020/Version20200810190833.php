<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200810190833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix SFR closed_at value';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE sales_forecasts SET closed_at = NULL WHERE status IN ('IN_PROGRESS', 'DELAYED', 'BUDGET')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
