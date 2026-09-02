<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240628123124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Feature to see Negotiated Transfer Price on odp xls for ROLE_PSA and role_PSM';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_NEGOTIATED_TRANSFER_PRICE_ODP_XLS")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_NEGOTIATED_TRANSFER_PRICE_ODP_XLS"
                          AND user_group.name IN ("ROLE_PSM", "ROLE_PSA")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
