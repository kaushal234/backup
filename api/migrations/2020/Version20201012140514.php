<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201012140514 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE qhse (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, poster_id INT NOT NULL, rating INT NOT NULL, date DATE NOT NULL, INDEX IDX_8DC93D9F64D218E (location_id), INDEX IDX_8DC93D9F5BB66C05 (poster_id), UNIQUE INDEX unique_rating_per_month_per_location (location_id, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE qhse ADD CONSTRAINT FK_8DC93D9F64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE qhse ADD CONSTRAINT FK_8DC93D9F5BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_QHSE_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_QHSE_WRITE"
          AND user_group.name IN ("SUPERUSER", "ROLE_QAM", "ROLE_MPE", "ROLE_COO", "ROLE_CEO", "ROLE_SPM", "ROLE_PM", "ROLE_CMO", "ROLE_EVP")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE qhse');
    }
}
