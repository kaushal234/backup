<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210611123204 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records ADD order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipment_records ADD CONSTRAINT FK_EAE697A98D9F6D38 FOREIGN KEY (order_id) REFERENCES sales_orders (id)');
        $this->addSql('CREATE INDEX IDX_EAE697A98D9F6D38 ON equipment_records (order_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records DROP FOREIGN KEY FK_EAE697A98D9F6D38');
        $this->addSql('DROP INDEX IDX_EAE697A98D9F6D38 ON equipment_records');
        $this->addSql('ALTER TABLE equipment_records DROP order_id');
    }
}
