<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211001231715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create table authorized applications and Insert FEATURE_AUTHORIZED_APPLICATION_ADMIN feature for superusers';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_AUTHORIZED_APPLICATION_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_AUTHORIZED_APPLICATION_ADMIN"
                          AND user_group.name = "SUPERUSER"');

        $this->addSql('CREATE TABLE authorized_application (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, disabled TINYINT(1) NOT NULL, key_expires_on DATETIME NOT NULL, key_generated_on DATETIME NOT NULL, UNIQUE INDEX UNIQ_D86E40AF5E237E06 (name), INDEX IDX_D86E40AFDE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE authorized_application ADD CONSTRAINT FK_D86E40AFDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE authorized_application');
    }
}
