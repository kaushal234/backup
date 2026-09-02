<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200129102313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE people_files (id INT NOT NULL, people_id INT DEFAULT NULL, INDEX IDX_CD563A193147C936 (people_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_logo_files (id INT NOT NULL, customer_id INT DEFAULT NULL, INDEX IDX_CC5D5EB79395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE news_files (id INT NOT NULL, news_id INT DEFAULT NULL, INDEX IDX_7C8AC707B5A459A0 (news_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE competitor_logo_files (id INT NOT NULL, competitor_id INT DEFAULT NULL, INDEX IDX_C5E105978A5D405 (competitor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE calibration_log_files (id INT NOT NULL, calibration_log_id INT DEFAULT NULL, INDEX IDX_299BB1849B5C7F91 (calibration_log_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE people_files ADD CONSTRAINT FK_CD563A193147C936 FOREIGN KEY (people_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE people_files ADD CONSTRAINT FK_CD563A19BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer_logo_files ADD CONSTRAINT FK_CC5D5EB79395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE customer_logo_files ADD CONSTRAINT FK_CC5D5EB7BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news_files ADD CONSTRAINT FK_7C8AC707B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id)');
        $this->addSql('ALTER TABLE news_files ADD CONSTRAINT FK_7C8AC707BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE competitor_logo_files ADD CONSTRAINT FK_C5E105978A5D405 FOREIGN KEY (competitor_id) REFERENCES competitors (id)');
        $this->addSql('ALTER TABLE competitor_logo_files ADD CONSTRAINT FK_C5E1059BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE calibration_log_files ADD CONSTRAINT FK_299BB1849B5C7F91 FOREIGN KEY (calibration_log_id) REFERENCES calibration_log (id)');
        $this->addSql('ALTER TABLE calibration_log_files ADD CONSTRAINT FK_299BB184BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE people_files');
        $this->addSql('DROP TABLE customer_logo_files');
        $this->addSql('DROP TABLE news_files');
        $this->addSql('DROP TABLE competitor_logo_files');
        $this->addSql('DROP TABLE calibration_log_files');
    }
}
