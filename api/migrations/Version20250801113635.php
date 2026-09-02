<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250801113635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migrate user settings to new format keys -> values.';
    }

    public function up(Schema $schema): void
    {
        $result = $this->connection->executeQuery('
SELECT user_setting.id, user_setting.user_id, modules.name, user_setting.settings
FROM user_setting
INNER JOIN modules ON modules.id = user_setting.module_id
');

        // Remove module_id and add name for key.
        $this->connection->executeQuery('TRUNCATE TABLE user_setting');
        $this->connection->executeQuery('ALTER TABLE user_setting DROP FOREIGN KEY FK_C779A692AFC2B591');
        $this->connection->executeQuery('DROP INDEX IDX_C779A692AFC2B591 ON user_setting');
        $this->connection->executeQuery('ALTER TABLE user_setting ADD name VARCHAR(255) NOT NULL AFTER `user_id`, DROP module_id');
        $this->connection->executeQuery('CREATE UNIQUE INDEX user_name ON user_setting (user_id, name)');

        // Migrate datas
        foreach ($result->iterateAssociative() as $row) {
            $settings = json_decode($row['settings'], true);

            // Create a key for TTS subscriptions.
            if ('TTS' === $row['name']) {
                $this->connection->insert('user_setting', [
                    'id' => '',
                    'user_id' => $row['user_id'],
                    'name' => 'tts.subscriptions',
                    'settings' => json_encode($settings),
                ]);

            // And for the others, create entries for each key.
            } else {
                foreach ($settings as $key => $setting) {
                    $key = mb_strtolower($row['name'].'.'.$key);
                    $this->connection->insert('user_setting', [
                        'id' => '',
                        'user_id' => $row['user_id'],
                        'name' => $key,
                        'settings' => json_encode($setting),
                    ]);
                }
            }
        }
    }

    public function down(Schema $schema): void
    {
    }
}
