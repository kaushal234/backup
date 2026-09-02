<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240213144726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add division group table for template per position per division';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE division_group (id INT AUTO_INCREMENT NOT NULL, division_id INT NOT NULL, position_id INT NOT NULL, INDEX IDX_4484A0DB41859289 (division_id), INDEX IDX_4484A0DBDD842E46 (position_id), UNIQUE INDEX unique_division_per_position (position_id, division_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE division_group_group (division_group_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_FA8EDF53A34FE475 (division_group_id), INDEX IDX_FA8EDF53FE54D947 (group_id), PRIMARY KEY(division_group_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE division_group ADD CONSTRAINT FK_4484A0DB41859289 FOREIGN KEY (division_id) REFERENCES directory_division (id)');
        $this->addSql('ALTER TABLE division_group ADD CONSTRAINT FK_4484A0DBDD842E46 FOREIGN KEY (position_id) REFERENCES directory_position (id)');
        $this->addSql('ALTER TABLE division_group_group ADD CONSTRAINT FK_FA8EDF53A34FE475 FOREIGN KEY (division_group_id) REFERENCES division_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE division_group_group ADD CONSTRAINT FK_FA8EDF53FE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE division_group DROP FOREIGN KEY FK_4484A0DB41859289');
        $this->addSql('ALTER TABLE division_group DROP FOREIGN KEY FK_4484A0DBDD842E46');
        $this->addSql('ALTER TABLE division_group_group DROP FOREIGN KEY FK_FA8EDF53A34FE475');
        $this->addSql('ALTER TABLE division_group_group DROP FOREIGN KEY FK_FA8EDF53FE54D947');
        $this->addSql('DROP TABLE division_group');
        $this->addSql('DROP TABLE division_group_group');
    }
}
