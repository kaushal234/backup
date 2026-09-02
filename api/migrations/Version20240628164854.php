<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240628164854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add survey to CSR commissioning';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_service_record ADD survey_aspect INT DEFAULT NULL, ADD survey_aspect_comment LONGTEXT DEFAULT NULL, ADD survey_conformity INT DEFAULT NULL, ADD survey_conformity_comment LONGTEXT DEFAULT NULL, ADD survey_operational INT DEFAULT NULL, ADD survey_operational_comment LONGTEXT DEFAULT NULL, ADD survey_shipping INT DEFAULT NULL, ADD survey_shipping_comment LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_service_record DROP survey_aspect, DROP survey_aspect_comment, DROP survey_conformity, DROP survey_conformity_comment, DROP survey_operational, DROP survey_operational_comment, DROP survey_shipping, DROP survey_shipping_comment');
    }
}
