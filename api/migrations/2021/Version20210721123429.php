<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210721123429 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64910F532ED');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64910F532ED FOREIGN KEY (extranet_user_linked_id) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64910F532ED');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64910F532ED FOREIGN KEY (extranet_user_linked_id) REFERENCES user (id)');
    }
}
