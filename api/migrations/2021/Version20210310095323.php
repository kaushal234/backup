<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210310095323 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE outbound_requests ADD slot VARCHAR(8) NOT NULL DEFAULT '', ADD total_number_of_lines INT NOT NULL DEFAULT 0, ADD number_of_lines_to_be_delivered INT NOT NULL DEFAULT 0, ADD request_created_at DATETIME DEFAULT NULL, ADD requested_to_be_delivered_at DATE DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE outbound_requests DROP slot, DROP total_number_of_lines, DROP number_of_lines_to_be_delivered, DROP request_created_at, DROP requested_to_be_delivered_at');
    }
}
