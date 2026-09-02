<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180926073904 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE demos DROP FOREIGN KEY FK_CD0E2E3C7E2FF845');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3C7E2FF845 FOREIGN KEY (future_demo_id) REFERENCES demos (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE demos DROP FOREIGN KEY FK_CD0E2E3C7E2FF845');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3C7E2FF845 FOREIGN KEY (future_demo_id) REFERENCES demos (id)');
    }
}
