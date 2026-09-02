<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211119171632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change to nullable properties buyer and endUser on EquipmentRecord entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records CHANGE buyer_id buyer_id INT DEFAULT NULL, CHANGE end_user_id end_user_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records CHANGE buyer_id buyer_id INT NOT NULL, CHANGE end_user_id end_user_id INT NOT NULL');
    }
}
