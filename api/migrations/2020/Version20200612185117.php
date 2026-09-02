<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200612185117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE approvers (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, assignor_id INT DEFAULT NULL, assignee_id INT NOT NULL, payment_blocked TINYINT(1) NOT NULL, legacy_id INT NOT NULL, discr VARCHAR(255) NOT NULL, supplier VARCHAR(255) DEFAULT NULL, supplier_name VARCHAR(255) DEFAULT NULL, analytical_dimension VARCHAR(255) DEFAULT NULL, INDEX IDX_64EC41DD64D218E (location_id), INDEX IDX_64EC41DDDE920AE7 (assignor_id), INDEX IDX_64EC41DD59EC7D60 (assignee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE approvers ADD CONSTRAINT FK_64EC41DD64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE approvers ADD CONSTRAINT FK_64EC41DDDE920AE7 FOREIGN KEY (assignor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE approvers ADD CONSTRAINT FK_64EC41DD59EC7D60 FOREIGN KEY (assignee_id) REFERENCES user (id)');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FINANCE_APPROVER")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                       WHERE feature.name = "FEATURE_FINANCE_APPROVER"
                          AND user_group.name in (
                            "SUPERUSER",
                            "ROLE_AP",
                            "ROLE_CFO",
                            "GG_ADMIN"
                          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE approvers');
    }
}
