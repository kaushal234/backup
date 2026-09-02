<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250720092222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC Hourmeter transactions done';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE toc_hour_meter_transactions ADD technician_on_call_id INT DEFAULT NULL, CHANGE toc_legacy_id toc_legacy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE toc_hour_meter_transactions ADD CONSTRAINT FK_59C11EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('CREATE INDEX IDX_59C11EC02A7D0 ON toc_hour_meter_transactions (technician_on_call_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE toc_hour_meter_transactions DROP FOREIGN KEY FK_59C11EC02A7D0');
        $this->addSql('DROP INDEX IDX_59C11EC02A7D0 ON toc_hour_meter_transactions');
        $this->addSql('ALTER TABLE toc_hour_meter_transactions DROP technician_on_call_id, CHANGE toc_legacy_id toc_legacy_id INT NOT NULL');
    }
}
