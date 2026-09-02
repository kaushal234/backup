<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240207144526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add permissions for MISM to update Trouble Ticket';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TROUBLE_TICKET_EDIT")');
        $this->addSql("INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name IN('FEATURE_TROUBLE_TICKET_EDIT')
                        AND user_group.name = 'ROLE_MISM'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
