<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250514091342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove unused feature';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name IN ("FEATURE_CUSTOMER_CUSTOMER_ERP_REFERENCE_ADMIN"))');
        $this->addSql('DELETE from feature WHERE feature.name IN ("FEATURE_CUSTOMER_CUSTOMER_ERP_REFERENCE_ADMIN")');
    }

    public function down(Schema $schema): void
    {
    }
}
