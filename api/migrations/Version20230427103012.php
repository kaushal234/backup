<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230427103012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new emission rating item "Tier 4f / Stage 5 Dual certified"';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO emission_ratings VALUES("", "Tier 4f / Stage 5 Dual certified", 4340, 0)');
    }
}
