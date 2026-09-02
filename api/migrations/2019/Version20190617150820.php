<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190617150820 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE transportation_notes (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, updated_at DATETIME NOT NULL, note TEXT NOT NULL, UNIQUE INDEX UNIQ_7FA126C3F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transportation_notes ADD CONSTRAINT FK_7FA126C3F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');

        $this->addSql('CREATE TABLE transportation_notes_files (id INT NOT NULL, note_id INT NOT NULL, INDEX IDX_2F387D7F26ED0855 (note_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transportation_notes_files ADD CONSTRAINT FK_2F387D7F26ED0855 FOREIGN KEY (note_id) REFERENCES transportation_notes (id)');
        $this->addSql('ALTER TABLE transportation_notes_files ADD CONSTRAINT FK_2F387D7FBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO feature (name) 	VALUES("FEATURE_TRANSPORTATION_NOTE_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_TRANSPORTATION_NOTE_WRITE"
			AND user_group.name in ( "ROLE_TRANSPORTATION_NOTE", "ROLE_SPM", "SUPERUSER" )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE transportation_notes_files');
        $this->addSql('DROP TABLE transportation_notes');
    }
}
