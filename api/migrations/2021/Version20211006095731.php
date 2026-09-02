<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211006095731 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE extranet_user_phone (extranet_user_id INT NOT NULL, phone_id INT NOT NULL, INDEX IDX_381F211ED2CDD54B (extranet_user_id), INDEX IDX_381F211E3B7323CB (phone_id), PRIMARY KEY(extranet_user_id, phone_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE extranet_user_phone ADD CONSTRAINT FK_381F211ED2CDD54B FOREIGN KEY (extranet_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE extranet_user_phone ADD CONSTRAINT FK_381F211E3B7323CB FOREIGN KEY (phone_id) REFERENCES phone (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE extranet_user_phone');
    }
}
