<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170620140000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE customer_relationship_teams (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, sales_representative_id INT DEFAULT NULL, parts_representative_id INT DEFAULT NULL, service_representative_id INT DEFAULT NULL, parts_location_id INT DEFAULT NULL, service_location_id INT DEFAULT NULL, erp_location_id INT DEFAULT NULL, customer_number VARCHAR(6) DEFAULT NULL, legacy_id INT NOT NULL, INDEX IDX_FFB1023A9395C3F3 (customer_id), INDEX IDX_FFB1023A8B54B08B (sales_representative_id), INDEX IDX_FFB1023AE03B0789 (parts_representative_id), INDEX IDX_FFB1023A411AB (service_representative_id), INDEX IDX_FFB1023A437031EE (parts_location_id), INDEX IDX_FFB1023AAE98149B (service_location_id), INDEX IDX_FFB1023A34BAA6FC (erp_location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023A9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023A8B54B08B FOREIGN KEY (sales_representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023AE03B0789 FOREIGN KEY (parts_representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023A411AB FOREIGN KEY (service_representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023A437031EE FOREIGN KEY (parts_location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023AAE98149B FOREIGN KEY (service_location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023A34BAA6FC FOREIGN KEY (erp_location_id) REFERENCES directory_location (id)');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CUSTOMER_RELATIONSHIP_TEAM_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_RELATIONSHIP_TEAM_WRITE"
                          AND user_group.name IN ("SUPERUSER")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE customer_relationship_teams');
    }
}
