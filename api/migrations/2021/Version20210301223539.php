<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210301223539 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE report_snapshot (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, resource VARCHAR(255) NOT NULL, x VARCHAR(255) NOT NULL, y VARCHAR(255) NOT NULL, options LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', x_totals LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', y_totals LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', total DOUBLE PRECISION NOT NULL, `rows` LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', iris LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', metadata LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE directory_position_classification ADD previous_year_corrected_total DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE directory_position_classification CHANGE correction correction DOUBLE PRECISION NOT NULL, CHANGE budget budget DOUBLE PRECISION DEFAULT NULL, CHANGE reforecast reforecast DOUBLE PRECISION DEFAULT NULL');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_EMPLOYEE_STAFFING_REPORT")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_EMPLOYEE_STAFFING_REPORT"
                          AND user_group.name in ("SUPERUSER", "ROLE_GTCD", "GG_HR", "GG_EXCOM")'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE report_snapshot');
        $this->addSql('ALTER TABLE directory_position_classification DROP previous_year_corrected_total');
        $this->addSql('ALTER TABLE directory_position_classification CHANGE correction correction INT NOT NULL, CHANGE budget budget INT DEFAULT NULL, CHANGE reforecast reforecast INT DEFAULT NULL');
    }
}
