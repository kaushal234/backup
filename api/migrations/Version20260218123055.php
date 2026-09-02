<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218123055 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add possibility to change visibility for VWC files.';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_VWC_FILE_CHANGE_VISIBILITY', ['SUPERUSER', 'ROLE_PSE']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
