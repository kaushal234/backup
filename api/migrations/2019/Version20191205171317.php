<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191205171317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting_actions ADD customer_id INT DEFAULT NULL, CHANGE assignee_id assignee_id INT DEFAULT NULL, CHANGE created_by created_by INT DEFAULT NULL, CHANGE closing_comment closing_comment VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE meeting_actions ADD CONSTRAINT FK_9D9AB9629395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('CREATE INDEX IDX_9D9AB9629395C3F3 ON meeting_actions (customer_id)');
        $this->addSql('ALTER TABLE meetings ADD full_description LONGTEXT DEFAULT NULL, CHANGE created_by created_by INT DEFAULT NULL, CHANGE location location VARCHAR(255) DEFAULT NULL, CHANGE closed_at closed_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE TABLE meeting_business_unit (meeting_id INT NOT NULL, business_unit_id INT NOT NULL, INDEX IDX_39D0828A67433D9C (meeting_id), INDEX IDX_39D0828AA58ECB40 (business_unit_id), PRIMARY KEY(meeting_id, business_unit_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE meeting_business_unit ADD CONSTRAINT FK_39D0828A67433D9C FOREIGN KEY (meeting_id) REFERENCES meetings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_business_unit ADD CONSTRAINT FK_39D0828AA58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE meeting_location');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting_actions DROP FOREIGN KEY FK_9D9AB9629395C3F3');
        $this->addSql('DROP INDEX IDX_9D9AB9629395C3F3 ON meeting_actions');
        $this->addSql('ALTER TABLE meeting_actions DROP customer_id, CHANGE assignee_id assignee_id INT DEFAULT NULL, CHANGE created_by created_by INT DEFAULT NULL, CHANGE closing_comment closing_comment VARCHAR(255) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE meetings DROP full_description, CHANGE created_by created_by INT DEFAULT NULL, CHANGE location location VARCHAR(255) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`, CHANGE closed_at closed_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('CREATE TABLE meeting_location (meeting_id INT NOT NULL, location_id INT NOT NULL, INDEX IDX_CD0FA41E64D218E (location_id), INDEX IDX_CD0FA41E67433D9C (meeting_id), PRIMARY KEY(meeting_id, location_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE meeting_location ADD CONSTRAINT FK_CD0FA41E64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_location ADD CONSTRAINT FK_CD0FA41E67433D9C FOREIGN KEY (meeting_id) REFERENCES meetings (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE meeting_business_unit');
    }
}
