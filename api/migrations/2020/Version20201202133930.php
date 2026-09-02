<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201202133930 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lead_times (id INT AUTO_INCREMENT NOT NULL, product_family_id INT DEFAULT NULL, factory_id INT DEFAULT NULL, weeks SMALLINT NOT NULL, previous_value SMALLINT DEFAULT NULL, description VARCHAR(1000) NOT NULL, INDEX IDX_7B302303ADFEE0E7 (product_family_id), INDEX IDX_7B302303C7AF27D2 (factory_id), UNIQUE INDEX unique_product_family_per_factory (factory_id, product_family_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lead_times ADD CONSTRAINT FK_7B302303ADFEE0E7 FOREIGN KEY (product_family_id) REFERENCES product_families (id)');
        $this->addSql('ALTER TABLE lead_times ADD CONSTRAINT FK_7B302303C7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_LEAD_TIME_WRITE_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_LEAD_TIME_WRITE_ADMIN"
			AND user_group.name in ("SUPERUSER")'
        );
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_LEAD_TIME_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_LEAD_TIME_WRITE"
			AND user_group.name in ("SUPERUSER", "ROLE_PSM", "ROLE_PSA", "ROLE_PSE", "ROLE_COO" )'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lead_times');
    }
}
