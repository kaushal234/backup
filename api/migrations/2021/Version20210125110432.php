<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210125110432 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE training_feedback ADD content_note INT DEFAULT NULL AFTER note, ADD trainer_note INT DEFAULT NULL AFTER content_note');
        $this->addSql('UPDATE training_feedback SET content_note = note, trainer_note = note');
        $this->addSql('ALTER TABLE training_feedback MODIFY content_note INTEGER NOT NULL, MODIFY trainer_note INTEGER NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE training_feedback DROP content_note, DROP trainer_note');
    }
}
