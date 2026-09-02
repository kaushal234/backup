<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230208132252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove authorized application evendors and its related features';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_EVENDORS_NEWS_READ")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_EVENDORS_NEWS_READ")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_EVENDORS_NEWS_READ"');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_WRITE")');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_SCAR_CREATE")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_SCAR_CREATE")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_SCAR_CREATE"');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_READ")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_READ")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_VENDOR_WARRANTY_CLAIM_READ"');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_SCAR_READ")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_SCAR_READ")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_SCAR_READ"');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_PEOPLE_READ")');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_LOCATIONS_READ")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_LOCATIONS_READ")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_LOCATIONS_READ"');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_PLANNED_MRP_READ")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_PLANNED_MRP_READ")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_PLANNED_MRP_READ"');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_COMMENT_READ")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_COMMENT_READ")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_COMMENT_READ"');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_PURCHASE_CONFIRM_DATE_WRITE")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_PURCHASE_CONFIRM_DATE_WRITE")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_PURCHASE_CONFIRM_DATE_WRITE"');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_ERP_PURCHASE_ORDERS_READ")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_ERP_PURCHASE_ORDERS_READ")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_ERP_PURCHASE_ORDERS_READ"');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_RFQ_READ")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_RFQ_READ")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_RFQ_READ"');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_DMS_READ")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_DMS_READ")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_DMS_READ"');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_COMMENT_WRITE")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_COMMENT_WRITE")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_COMMENT_WRITE"');

        $this->addSql('DELETE FROM feature_authorized_application WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_CURRENCY_WRITE")');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_CURRENCY_WRITE")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_CURRENCY_WRITE"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
