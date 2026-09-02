<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260213102624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create planning_daily_exception to add message on day on planning';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE planning_daily_exception (date DATE NOT NULL, comment VARCHAR(255) DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, factory_id INT NOT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_A77A815BC7AF27D2 (factory_id), INDEX IDX_A77A815BB03A8386 (created_by_id), UNIQUE INDEX uniq_factory_date (factory_id, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE planning_daily_exception ADD CONSTRAINT FK_A77A815BC7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE planning_daily_exception ADD CONSTRAINT FK_A77A815BB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE planning_daily_exception DROP FOREIGN KEY FK_A77A815BC7AF27D2');
        $this->addSql('ALTER TABLE planning_daily_exception DROP FOREIGN KEY FK_A77A815BB03A8386');
        $this->addSql('DROP TABLE planning_daily_exception');
    }
}
