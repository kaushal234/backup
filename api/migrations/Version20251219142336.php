<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251219142336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add subDivision and description mandatory on customers files';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customers_files ADD sub_division_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customers_files ADD CONSTRAINT FK_35D5237CA47CE717 FOREIGN KEY (sub_division_id) REFERENCES directory_sub_division (id)');
        $this->addSql('CREATE INDEX IDX_35D5237CA47CE717 ON customers_files (sub_division_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customers_files DROP FOREIGN KEY FK_35D5237CA47CE717');
        $this->addSql('DROP INDEX IDX_35D5237CA47CE717 ON customers_files');
        $this->addSql('ALTER TABLE customers_files DROP sub_division_id');
    }
}
