<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240215143919 extends AbstractMigration
{
    public const VAULT_MAINI = [
        'erp' => 820,
        'path' => 'global.released/montlouis.released',
        'folder' => 2,
        'sub_folder' => 4,
    ];

    public function getDescription(): string
    {
        return 'Add MAINI to vault configuration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(\sprintf('INSERT INTO vault (path, folder, sub_folder, location_id) VALUES ("%s", "%d", %d, (SELECT id FROM directory_location WHERE erp = %d))', self::VAULT_MAINI['path'], self::VAULT_MAINI['folder'], self::VAULT_MAINI['sub_folder'], self::VAULT_MAINI['erp']));
    }

    public function down(Schema $schema): void
    {
    }
}
