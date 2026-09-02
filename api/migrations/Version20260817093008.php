<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260817093008 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add FEATURE_FIRST_ARTICLE_QUALIFICATION_STATUS_OVERRIDE for QAM status override on FAQ.';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_FIRST_ARTICLE_QUALIFICATION_STATUS_OVERRIDE', ['SUPERUSER', 'ROLE_QAM']);
    }

    public function down(Schema $schema): void
    {
    }
}
