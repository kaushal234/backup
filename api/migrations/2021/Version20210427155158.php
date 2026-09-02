<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210427155158 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer_sales_representatives (id INT AUTO_INCREMENT NOT NULL, asm_id INT NOT NULL, sub_division_id INT NOT NULL, customer_id INT DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_DC7191CD9C54D4BF (asm_id), INDEX IDX_DC7191CDA47CE717 (sub_division_id), INDEX IDX_DC7191CD9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer_sales_representatives ADD CONSTRAINT FK_DC7191CD9C54D4BF FOREIGN KEY (asm_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_sales_representatives ADD CONSTRAINT FK_DC7191CDA47CE717 FOREIGN KEY (sub_division_id) REFERENCES directory_sub_division (id)');
        $this->addSql('ALTER TABLE customer_sales_representatives ADD CONSTRAINT FK_DC7191CD9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE customers ADD main_sales_representative_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customers ADD CONSTRAINT FK_62534E215705F875 FOREIGN KEY (main_sales_representative_id) REFERENCES customer_sales_representatives (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_62534E215705F875 ON customers (main_sales_representative_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customers DROP FOREIGN KEY FK_62534E215705F875');
        $this->addSql('DROP TABLE customer_sales_representatives');
        $this->addSql('DROP INDEX UNIQ_62534E215705F875 ON customers');
        $this->addSql('ALTER TABLE customers DROP main_sales_representative_id');
    }
}
