<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180116110123 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
          CREATE TABLE equipment_hourmeter_resets (
            id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\',
              created_by INT NOT NULL COMMENT \'(DC2Type:integer)\',
              equipment_record_id INT NOT NULL COMMENT \'(DC2Type:integer)\',
              created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\',
              comment TEXT NOT NULL, hourmeter INT NOT NULL COMMENT \'(DC2Type:integer)\',
              hourmeter_date DATE NOT NULL, INDEX IDX_E30ABC18DE12AB56 (created_by),
              INDEX IDX_E30ABC189FC03375 (equipment_record_id),
              PRIMARY KEY(id)
          ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE equipment_hourmeter_resets ADD CONSTRAINT FK_E30ABC18DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE equipment_hourmeter_resets ADD CONSTRAINT FK_E30ABC189FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE follow_up_reports RENAME equipment_follow_up_reports');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE equipment_hourmeter_resets');
        $this->addSql('ALTER TABLE equipment_follow_up_reports RENAME follow_up_reports');
    }
}
