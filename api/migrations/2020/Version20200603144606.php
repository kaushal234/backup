<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200603144606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE shipping_quotation_requests ADD counter INT NOT NULL, CHANGE estimated_pick_up_deadline answer_deadline VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE shipping_quotation_request_lines ADD answer_deadline VARCHAR(255) DEFAULT NULL, DROP estimated_pick_up_deadline, CHANGE harmonized_system_code harmonized_system_code VARCHAR(255) DEFAULT NULL, CHANGE ground_clearance ground_clearance INT DEFAULT NULL');
        $this->addSql('ALTER TABLE containers ADD loading_at_factory TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE containers DROP loading_at_factory');
        $this->addSql('ALTER TABLE shipping_quotation_request_lines ADD estimated_pick_up_deadline VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, DROP answer_deadline, CHANGE harmonized_system_code harmonized_system_code VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE ground_clearance ground_clearance INT NOT NULL');
        $this->addSql('ALTER TABLE shipping_quotation_requests DROP counter, CHANGE answer_deadline estimated_pick_up_deadline VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
