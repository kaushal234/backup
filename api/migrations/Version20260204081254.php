<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204081254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a bool to define if the position requires a mentor';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE directory_position ADD mentor_mandatory TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql(
            'UPDATE directory_position SET mentor_mandatory = 1 WHERE code IN ("CSM", "SPM", "SAM", "PM", "PSM", "EM", "QAM", "MLM", "HRM", "MISM", "FAM", "RCEO", "COO", "CFO", "DSS")'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE directory_position DROP mentor_mandatory');
    }
}
