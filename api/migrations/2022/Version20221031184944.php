<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221031184944 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add shipping origin column on SPR parts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spare_parts_requests_parts ADD shipping_origin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE spare_parts_requests_parts ADD CONSTRAINT FK_52CA4FA0105722FB FOREIGN KEY (shipping_origin_id) REFERENCES directory_location (id)');
        $this->addSql('CREATE INDEX IDX_52CA4FA0105722FB ON spare_parts_requests_parts (shipping_origin_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spare_parts_requests_parts DROP FOREIGN KEY FK_52CA4FA0105722FB');
        $this->addSql('DROP INDEX IDX_52CA4FA0105722FB ON spare_parts_requests_parts');
        $this->addSql('ALTER TABLE spare_parts_requests_parts DROP shipping_origin_id');
    }
}
