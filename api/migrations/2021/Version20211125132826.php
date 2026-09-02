<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211125132826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create new parts table and insert old spare parts request parts in the new one';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE parts (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, deleted_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL, part_number VARCHAR(25) NOT NULL, description VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION NOT NULL, unit_of_measure VARCHAR(3) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_6940A7FEB03A8386 (created_by_id), INDEX IDX_6940A7FEC76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE parts ADD CONSTRAINT FK_6940A7FEB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE parts ADD CONSTRAINT FK_6940A7FEC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id)');

        $this->addSql('INSERT INTO parts (id, created_by_id, deleted_by_id, created_at, part_number, description, quantity, unit_of_measure, deleted_at, discr) SELECT id, created_by_id, deleted_by_id, created_at, part_number, description, quantity, unit_of_measure, deleted_at, "spare_parts_request_part" AS spare_parts_request_part FROM spare_parts_requests_parts');

        $this->addSql('ALTER TABLE spare_parts_requests_parts DROP FOREIGN KEY FK_52CA4FA0B03A8386');
        $this->addSql('ALTER TABLE spare_parts_requests_parts DROP FOREIGN KEY FK_52CA4FA0C76F1F52');
        $this->addSql('DROP INDEX IDX_52CA4FA0C76F1F52 ON spare_parts_requests_parts');
        $this->addSql('DROP INDEX IDX_52CA4FA0B03A8386 ON spare_parts_requests_parts');
        $this->addSql('ALTER TABLE spare_parts_requests_parts DROP created_by_id, DROP deleted_by_id, DROP created_at, DROP part_number, DROP description, DROP unit_of_measure, DROP quantity, DROP deleted_at, CHANGE id id INT NOT NULL');

        $this->addSql('ALTER TABLE spare_parts_requests_parts ADD CONSTRAINT FK_52CA4FA0BF396750 FOREIGN KEY (id) REFERENCES parts (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE spare_parts_requests_parts DROP FOREIGN KEY FK_52CA4FA0BF396750');
        $this->addSql('DROP TABLE parts');
        $this->addSql('ALTER TABLE spare_parts_requests_parts ADD created_by_id INT NOT NULL, ADD deleted_by_id INT DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD part_number VARCHAR(25) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, ADD description VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, ADD unit_of_measure VARCHAR(3) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD quantity DOUBLE PRECISION NOT NULL, ADD deleted_at DATETIME DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE spare_parts_requests_parts ADD CONSTRAINT FK_52CA4FA0B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE spare_parts_requests_parts ADD CONSTRAINT FK_52CA4FA0C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_52CA4FA0C76F1F52 ON spare_parts_requests_parts (deleted_by_id)');
        $this->addSql('CREATE INDEX IDX_52CA4FA0B03A8386 ON spare_parts_requests_parts (created_by_id)');
    }
}
