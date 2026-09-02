<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20160212051655 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE phone SET type='hometophone' WHERE type LIKE 'home'");
        $this->addSql("UPDATE phone SET type='home' WHERE type LIKE 'phone'");
        $this->addSql("UPDATE phone SET type='phone' WHERE type LIKE 'hometophone'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE phone SET type='phonetohome' WHERE type LIKE 'phone'");
        $this->addSql("UPDATE phone SET type='phone' WHERE type LIKE 'home'");
        $this->addSql("UPDATE phone SET type='home' WHERE type LIKE 'phonetohome'");
    }
}
