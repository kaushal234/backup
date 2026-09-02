<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220504160607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add 2 indexes on manual_parts and equipment_serials';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_E2C3EAD2D374C9DC ON equipment_serials (serial)');
        $this->addSql('CREATE INDEX IDX_2B853C5DAF00DFC1 ON manual_parts (part_number)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_E2C3EAD2D374C9DC ON equipment_serials');
        $this->addSql('DROP INDEX IDX_2B853C5DAF00DFC1 ON manual_parts');
    }
}
