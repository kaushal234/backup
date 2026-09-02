<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191003125005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'ATM module';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE trainings (id INT AUTO_INCREMENT NOT NULL, organizer_id INT NOT NULL, trainer_id INT NOT NULL, category_id INT DEFAULT NULL, type_id INT DEFAULT NULL, meeting_place VARCHAR(255) NOT NULL, timezone VARCHAR(255) NOT NULL, starting_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ending_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', attendance_limit INT DEFAULT NULL, status VARCHAR(25) NOT NULL, designation VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, level VARCHAR(20) DEFAULT NULL, open TINYINT(1) NOT NULL, mandatory TINYINT(1) NOT NULL, language VARCHAR(2) NOT NULL, INDEX IDX_66DC4330876C4DDA (organizer_id), INDEX IDX_66DC4330FB08EDF6 (trainer_id), INDEX IDX_66DC433012469DE2 (category_id), INDEX IDX_66DC4330C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trainings_attendees (id INT AUTO_INCREMENT NOT NULL, guest_id INT NOT NULL, training_id INT NOT NULL, invited TINYINT(1) NOT NULL, answer TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_B36D6D4E9A4AA658 (guest_id), INDEX IDX_B36D6D4EBEFD98D1 (training_id), UNIQUE INDEX unique_guest_per_training (training_id, guest_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trainings_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, UNIQUE INDEX UNIQ_E0E85ABE5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trainings_files (id INT NOT NULL, training_id INT NOT NULL, INDEX IDX_B7FC3842BEFD98D1 (training_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trainings_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, UNIQUE INDEX UNIQ_E8F9F12B5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE trainings ADD CONSTRAINT FK_66DC4330876C4DDA FOREIGN KEY (organizer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trainings ADD CONSTRAINT FK_66DC4330FB08EDF6 FOREIGN KEY (trainer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trainings ADD CONSTRAINT FK_66DC433012469DE2 FOREIGN KEY (category_id) REFERENCES trainings_categories (id)');
        $this->addSql('ALTER TABLE trainings ADD CONSTRAINT FK_66DC4330C54C8C93 FOREIGN KEY (type_id) REFERENCES trainings_types (id)');
        $this->addSql('ALTER TABLE trainings_attendees ADD CONSTRAINT FK_B36D6D4E9A4AA658 FOREIGN KEY (guest_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trainings_attendees ADD CONSTRAINT FK_B36D6D4EBEFD98D1 FOREIGN KEY (training_id) REFERENCES trainings (id)');
        $this->addSql('ALTER TABLE trainings_files ADD CONSTRAINT FK_B7FC3842BEFD98D1 FOREIGN KEY (training_id) REFERENCES trainings (id)');
        $this->addSql('ALTER TABLE trainings_files ADD CONSTRAINT FK_B7FC3842BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO feature (name) 	VALUES("FEATURE_TRAINING_ATTENDEE_DELETE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_TRAINING_ATTENDEE_DELETE" AND user_group.name  = "SUPERUSER"'
        );
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trainings_attendees DROP FOREIGN KEY FK_B36D6D4EBEFD98D1');
        $this->addSql('ALTER TABLE trainings_files DROP FOREIGN KEY FK_B7FC3842BEFD98D1');
        $this->addSql('ALTER TABLE trainings DROP FOREIGN KEY FK_66DC433012469DE2');
        $this->addSql('ALTER TABLE trainings DROP FOREIGN KEY FK_66DC4330C54C8C93');
        $this->addSql('DROP TABLE trainings');
        $this->addSql('DROP TABLE trainings_attendees');
        $this->addSql('DROP TABLE trainings_categories');
        $this->addSql('DROP TABLE trainings_files');
        $this->addSql('DROP TABLE trainings_types');

        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_TRAINING_ATTENDEE_DELETE")');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_TRAINING_ATTENDEE_DELETE"');
    }
}
