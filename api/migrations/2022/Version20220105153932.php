<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220105153932 extends AbstractMigration
{
    public const VAULT_CONFIGURATION = [
        220 => [
            'path' => 'global.released/powervamp.released',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        250 => [
            'path' => 'global.released/aero.released',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        300 => [
            'path' => 'windsor.released',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        400 => [
            'path' => 'windsor.released',
            'uncPath' => 'mercury/vault',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        410 => [
            'path' => 'windsor.released',
            'uncPath' => 'mercury/vault',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        420 => [
            'path' => 'global.released/sherbrooke.released',
            'uncPath' => 'pluto/vault',
            'folder' => 2,
            'sub_folder' => 3,
        ],
        500 => [
            'path' => 'global.released/montlouis.released',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        510 => [
            'path' => 'global.released/montlouis.released',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        520 => [
            'path' => 'global.released/montlouis.released',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        540 => [
            'path' => 'global.released/montlouis.released',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        560 => [
            'path' => 'global.released/montlouis.released',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        570 => [
            'path' => 'global.released/lebrun.released',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        600 => [
            'path' => 'hongkong.eng',
            'folder' => 3,
            'sub_folder' => 4,
        ],
        620 => [
            'path' => 'taiwan.eng',
            'folder' => 3,
            'sub_folder' => 4,
        ],
        640 => [
            'path' => 'global.released/shanghai.released',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        660 => [
            'path' => 'global.released/wuxi.released',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        680 => [
            'path' => 'global.released/shanghai.released',
            'folder' => 2,
            'sub_folder' => 4,
        ],
        700 => [
            'path' => 'global.released/shanghai.released',
            'folder' => 2,
            'sub_folder' => 4,
        ],
    ];

    public function getDescription(): string
    {
        return 'Table Vault';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE vault (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, path VARCHAR(255) NOT NULL, folder INT NOT NULL, sub_folder INT NOT NULL, INDEX IDX_FF30492164D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vault ADD CONSTRAINT FK_FF30492164D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');

        foreach (self::VAULT_CONFIGURATION as $erp => $vaultConfiguration) {
            $this->addSql(\sprintf('INSERT INTO vault (path, folder, sub_folder, location_id) VALUES ("%s", "%d", %d, (SELECT id FROM directory_location WHERE erp = %d))', $vaultConfiguration['path'], $vaultConfiguration['folder'], $vaultConfiguration['sub_folder'], $erp));
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE vault');
    }
}
