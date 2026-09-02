<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151123102112 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_group CHANGE legacy_id legacy_id INT NOT NULL');
        $this->addSql('ALTER TABLE business_unit ADD representative_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE business_unit ADD CONSTRAINT FK_8C200E5EFC3FF006 FOREIGN KEY (representative_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8C200E5EFC3FF006 ON business_unit (representative_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE business_unit DROP FOREIGN KEY FK_8C200E5EFC3FF006');
        $this->addSql('DROP INDEX IDX_8C200E5EFC3FF006 ON business_unit');
        $this->addSql('ALTER TABLE business_unit DROP representative_id');
        $this->addSql('ALTER TABLE user_group CHANGE legacy_id legacy_id INT DEFAULT NULL');
    }
}
