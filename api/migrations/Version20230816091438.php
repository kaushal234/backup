<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230816091438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add PURCHASING - part missing / incomplete kit into Process Table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT IGNORE INTO process (category, description) VALUES
            ('Purchasing', 'Part missing / Incomplete kit');
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE from process WHERE process.description = "Part missing / Incomplete kit"');
    }
}
