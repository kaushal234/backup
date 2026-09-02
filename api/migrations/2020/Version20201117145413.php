<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201117145413 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE training_feedback (id INT AUTO_INCREMENT NOT NULL, attendee_id INT NOT NULL, note INT NOT NULL, comment VARCHAR(255) DEFAULT NULL, UNIQUE INDEX unique_feedback_per_attendee (attendee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE training_feedback ADD CONSTRAINT FK_354C57CFBCFD782A FOREIGN KEY (attendee_id) REFERENCES trainings_attendees (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE training_feedback');
    }
}
