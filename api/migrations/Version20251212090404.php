<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251212090404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the archived property to hide delivery addresses';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spare_parts_requests_delivery_addresses ADD archived_by_id INT DEFAULT NULL, ADD archived TINYINT(1) NOT NULL, ADD archived_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE spare_parts_requests_delivery_addresses ADD CONSTRAINT FK_9307638777BE2925 FOREIGN KEY (archived_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_9307638777BE2925 ON spare_parts_requests_delivery_addresses (archived_by_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spare_parts_requests_delivery_addresses DROP FOREIGN KEY FK_9307638777BE2925');
        $this->addSql('DROP INDEX IDX_9307638777BE2925 ON spare_parts_requests_delivery_addresses');
        $this->addSql('ALTER TABLE spare_parts_requests_delivery_addresses DROP archived_by_id, DROP archived, DROP archived_at');
    }
}
