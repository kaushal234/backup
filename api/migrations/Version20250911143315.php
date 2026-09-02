<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250911143315 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'add FEATURE_PAUSE_UNPAUSE_TASK to GG_MIS and notification_templates task_pause';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_PAUSE_UNPAUSE_TASK', ['GG_MIS']);
        $this->addSql("INSERT INTO notification_templates (module_id, name, text) VALUES (31, 'task_pause', 'This task has been paused')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM notification_templates WHERE module_id = 31 AND name = "task_pause"');
    }
}
