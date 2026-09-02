<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191118085644 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE product_certificate (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, emission_rating_id INT DEFAULT NULL, factory_id INT NOT NULL, engineering_activity_process INT DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, test_report_number VARCHAR(255) DEFAULT NULL, expected_at DATETIME DEFAULT NULL, expired_at DATETIME DEFAULT NULL, description LONGTEXT DEFAULT NULL, announcement_certificate_number VARCHAR(255) DEFAULT NULL, INDEX IDX_8DB344554584665A (product_id), INDEX IDX_8DB3445515428FA8 (emission_rating_id), INDEX IDX_8DB34455C7AF27D2 (factory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_certificate_files (id INT NOT NULL, certificate_id INT DEFAULT NULL, INDEX IDX_9B17A2AF99223FFD (certificate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_certificate ADD CONSTRAINT FK_8DB344554584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE product_certificate ADD CONSTRAINT FK_8DB3445515428FA8 FOREIGN KEY (emission_rating_id) REFERENCES emission_ratings (id)');
        $this->addSql('ALTER TABLE product_certificate ADD CONSTRAINT FK_8DB34455C7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE product_certificate_files ADD CONSTRAINT FK_9B17A2AF99223FFD FOREIGN KEY (certificate_id) REFERENCES product_certificate (id)');
        $this->addSql('ALTER TABLE product_certificate_files ADD CONSTRAINT FK_9B17A2AFBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PRODUCT_CERTIFICATE_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_PRODUCT_CERTIFICATE_ADMIN"
			AND user_group.name in ( "ROLE_EM", "SUPERUSER" )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_certificate_files DROP FOREIGN KEY FK_9B17A2AF99223FFD');
        $this->addSql('DROP TABLE product_certificate');
        $this->addSql('DROP TABLE product_certificate_files');
    }
}
