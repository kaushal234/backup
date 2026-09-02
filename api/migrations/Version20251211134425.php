<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211134425 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'add restriction download people directory as csv';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_DOWNLOAD_DIRECTORY_CONFIDENTIAL', ['SUPERUSER', 'GG_MIS', 'GG_HR', 'ROLE_LM']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
