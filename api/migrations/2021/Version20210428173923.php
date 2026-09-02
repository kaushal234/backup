<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210428173923 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE modules ADD key_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE modules ADD CONSTRAINT FK_2EB743D7C5B8F7AB FOREIGN KEY (key_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2EB743D7C5B8F7AB ON modules (key_user_id)');
    }
}
