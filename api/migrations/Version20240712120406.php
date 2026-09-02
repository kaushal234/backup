<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240712120406 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove useless SQR features';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_SHIPPING_QUOTATION_REQUEST_VIEW")');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_SHIPPING_QUOTATION_REQUEST_VIEW"');

        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_SHIPPING_QUOTATION_REQUEST_ADMIN")');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_SHIPPING_QUOTATION_REQUEST_ADMIN"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
