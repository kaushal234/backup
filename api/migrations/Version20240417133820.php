<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240417133820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update thresholds rules of supplier ranking.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE supplier_rankings_thresholds_criterias SET rank_limit=2 WHERE threshold_id IN (4, 5, 6)');
        $this->addSql('UPDATE supplier_rankings_thresholds_criterias SET rank_limit=2 WHERE threshold_id IN (7, 8, 9) AND rank_limit=3');
        $this->addSql('UPDATE supplier_rankings_thresholds_criterias SET rank_limit=3 WHERE threshold_id IN (7, 8, 9) AND rank_limit=4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE supplier_rankings_thresholds_criterias SET rank_limit=3 WHERE threshold_id IN (4, 5, 6)');
        $this->addSql('UPDATE supplier_rankings_thresholds_criterias SET rank_limit=4 WHERE threshold_id IN (7, 8, 9) AND rank_limit=3');
        $this->addSql('UPDATE supplier_rankings_thresholds_criterias SET rank_limit=3 WHERE threshold_id IN (7, 8, 9) AND rank_limit=2');
    }
}
