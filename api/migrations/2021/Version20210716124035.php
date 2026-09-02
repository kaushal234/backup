<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210716124035 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_families_tags (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_families_tags_xref (product_family_tag_id INT NOT NULL, product_family_id INT NOT NULL, INDEX IDX_B1510B46A90F28F6 (product_family_tag_id), INDEX IDX_B1510B46ADFEE0E7 (product_family_id), PRIMARY KEY(product_family_tag_id, product_family_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_families_tags ADD CONSTRAINT FK_ABBA4D3CBF396750 FOREIGN KEY (id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_families_tags_xref ADD CONSTRAINT FK_B1510B46A90F28F6 FOREIGN KEY (product_family_tag_id) REFERENCES product_families_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_families_tags_xref ADD CONSTRAINT FK_B1510B46ADFEE0E7 FOREIGN KEY (product_family_id) REFERENCES product_families (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 40,"Hydrogen",NOW(),"product_family")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 40,"Electric",NOW(),"product_family")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 40,"Hybrid",NOW(),"product_family")');

        $this->addSql('INSERT IGNORE INTO product_families_tags (id) SELECT id FROM tags WHERE discr="product_family"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_families_tags_xref DROP FOREIGN KEY FK_B1510B46A90F28F6');
        $this->addSql('DROP TABLE product_families_tags');
        $this->addSql('DROP TABLE product_families_tags_xref');
    }
}
