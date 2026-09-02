<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210621185801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE premises (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(10) NOT NULL, description VARCHAR(255) NOT NULL, archived TINYINT(1) NOT NULL, archived_at DATETIME DEFAULT NULL, address_country VARCHAR(2) DEFAULT NULL, address_street1 VARCHAR(255) DEFAULT NULL, address_street2 VARCHAR(255) DEFAULT NULL, address_postal_code VARCHAR(20) DEFAULT NULL, address_city VARCHAR(50) DEFAULT NULL, address_town VARCHAR(50) DEFAULT NULL, address_state VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE premises_tags (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE premises_tags_xref (premise_tag_id INT NOT NULL, premise_id INT NOT NULL, INDEX IDX_D407F8E36897E6F3 (premise_tag_id), INDEX IDX_D407F8E3BD8D5AD9 (premise_id), PRIMARY KEY(premise_tag_id, premise_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE premises_tags ADD CONSTRAINT FK_AF73E56BF396750 FOREIGN KEY (id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE premises_tags_xref ADD CONSTRAINT FK_D407F8E36897E6F3 FOREIGN KEY (premise_tag_id) REFERENCES premises_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE premises_tags_xref ADD CONSTRAINT FK_D407F8E3BD8D5AD9 FOREIGN KEY (premise_id) REFERENCES premises (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD premise_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649BD8D5AD9 FOREIGN KEY (premise_id) REFERENCES premises (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649BD8D5AD9 ON user (premise_id)');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PREMISE_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_PREMISE_WRITE"
			AND user_group.name in ("SUPERUSER", "GG_HR", "GG_EXECOM")'
        );
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Home Office",NOW(),"premise")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 116,"Home Office (restricted)",NOW(),"premise")');
        $this->addSql('INSERT IGNORE INTO premises_tags (id) SELECT id FROM tags WHERE discr="premise"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE premises_tags_xref DROP FOREIGN KEY FK_D407F8E3BD8D5AD9');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649BD8D5AD9');
        $this->addSql('ALTER TABLE premises_tags_xref DROP FOREIGN KEY FK_D407F8E36897E6F3');
        $this->addSql('DROP TABLE premises');
        $this->addSql('DROP TABLE premises_tags');
        $this->addSql('DROP TABLE premises_tags_xref');
        $this->addSql('DROP INDEX IDX_8D93D649BD8D5AD9 ON user');
        $this->addSql('ALTER TABLE user DROP premise_id');
    }
}
