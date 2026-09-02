<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528150000 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add access restriction for upcoming new comers filter';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_FILTER_PEOPLE_INCOMING', ['SUPERUSER', 'GG_MIS', 'GG_HR']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
