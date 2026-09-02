<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171208084205 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE extranet_user_profile DROP FOREIGN KEY FK_4382446BD2CDD54B');
        $this->addSql('ALTER TABLE extranet_user_profile ADD CONSTRAINT FK_4382446BD2CDD54B FOREIGN KEY (extranet_user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE extranet_user_profile DROP FOREIGN KEY FK_4382446BD2CDD54B');
        $this->addSql('ALTER TABLE extranet_user_profile ADD CONSTRAINT FK_4382446BD2CDD54B FOREIGN KEY (extranet_user_id) REFERENCES user (id)');
    }
}
