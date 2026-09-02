<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210128130515 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE bwi_logs (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, erp INT NOT NULL, login VARCHAR(15) NOT NULL, operation VARCHAR(50) NOT NULL, operation_number INT NOT NULL, message LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE bwi_logs');
    }
}
