<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210222160135 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD coefficient INT DEFAULT NULL');
        $this->addSql("UPDATE user SET contract_type_id=1, coefficient=100 WHERE discr='people' AND disabled=0");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP coefficient');
    }
}
