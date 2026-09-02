<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250515100430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace TLD responsible by ALVEST for NCR modules';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE responsible SET name="ALVEST" WHERE name="TLD"');
    }

    public function down(Schema $schema): void
    {
    }
}
