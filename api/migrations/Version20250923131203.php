<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923131203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add property created_by to user story';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_story ADD created_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_story ADD CONSTRAINT FK_994FF60DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_994FF60DE12AB56 ON user_story (created_by)');

        $this->addSql('
            UPDATE user_story us
            INNER JOIN specification s ON us.specification_id = s.id
            INNER JOIN modules m ON s.module_id = m.id
            SET us.created_by = m.operational_owner_id
            WHERE m.operational_owner_id IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_story DROP FOREIGN KEY FK_994FF60DE12AB56');
        $this->addSql('DROP INDEX IDX_994FF60DE12AB56 ON user_story');
        $this->addSql('ALTER TABLE user_story DROP created_by');
    }
}
