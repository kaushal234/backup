<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240923143155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'CSR notification for not perfect commissioning';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO notification_templates (module_id, name, text) VALUES (
            17,
            'customer_service_record_survey_imperfect',
            'csr.notification.commissioning_imperfect'
        )");
    }

    public function down(Schema $schema): void
    {
    }
}
