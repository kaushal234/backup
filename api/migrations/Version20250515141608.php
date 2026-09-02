<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250515141608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add properties to News entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news ADD department_id INT DEFAULT NULL, ADD division_id INT DEFAULT NULL, ADD premise_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD39950AE80F5DF FOREIGN KEY (department_id) REFERENCES directory_department (id)');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD3995041859289 FOREIGN KEY (division_id) REFERENCES directory_division (id)');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD39950BD8D5AD9 FOREIGN KEY (premise_id) REFERENCES premises (id)');
        $this->addSql('CREATE INDEX IDX_1DD39950AE80F5DF ON news (department_id)');
        $this->addSql('CREATE INDEX IDX_1DD3995041859289 ON news (division_id)');
        $this->addSql('CREATE INDEX IDX_1DD39950BD8D5AD9 ON news (premise_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news DROP FOREIGN KEY FK_1DD39950AE80F5DF');
        $this->addSql('ALTER TABLE news DROP FOREIGN KEY FK_1DD3995041859289');
        $this->addSql('ALTER TABLE news DROP FOREIGN KEY FK_1DD39950BD8D5AD9');
        $this->addSql('DROP INDEX IDX_1DD39950AE80F5DF ON news');
        $this->addSql('DROP INDEX IDX_1DD3995041859289 ON news');
        $this->addSql('DROP INDEX IDX_1DD39950BD8D5AD9 ON news');
        $this->addSql('ALTER TABLE news DROP department_id, DROP division_id, DROP premise_id');
    }
}
