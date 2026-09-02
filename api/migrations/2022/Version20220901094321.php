<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220901094321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add table to link positions and standard groups';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE position_group (position_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_E100A018DD842E46 (position_id), INDEX IDX_E100A018FE54D947 (group_id), PRIMARY KEY(position_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE position_group ADD CONSTRAINT FK_E100A018DD842E46 FOREIGN KEY (position_id) REFERENCES directory_position (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE position_group ADD CONSTRAINT FK_E100A018FE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE position_group');
    }
}
