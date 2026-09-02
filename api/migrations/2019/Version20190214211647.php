<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190214211647 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM sales_forecasts_files;');
        $this->addSql('ALTER TABLE sales_forecasts_files AUTO_INCREMENT=1;');
        $this->addSql('DELETE FROM forecast_closures_files;');
        $this->addSql('ALTER TABLE forecast_closures_files AUTO_INCREMENT=1;');
        $this->addSql('DELETE FROM competitor_pricings_files;');
        $this->addSql('ALTER TABLE competitor_pricings_files AUTO_INCREMENT=1;');
        $this->addSql('DELETE FROM competitor_pricings;');
        $this->addSql('ALTER TABLE competitor_pricings AUTO_INCREMENT=1;');
        $this->addSql('DELETE FROM forecast_closures;');
        $this->addSql('ALTER TABLE forecast_closures AUTO_INCREMENT=1;');
        $this->addSql('DELETE FROM sales_forecasts;');
        $this->addSql('ALTER TABLE sales_forecasts AUTO_INCREMENT=1;');
        $this->addSql('DELETE FROM sales_forecasts_master;');
        $this->addSql('ALTER TABLE sales_forecasts_master AUTO_INCREMENT=1;');
        $this->addSql('DELETE FROM activity WHERE resource LIKE"%forecast%";');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
