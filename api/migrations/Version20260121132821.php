<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260121132821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Decomissioning old supplier link on ranking.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        // Execute clean only on the dev environment.
        if ('prod' !== $_ENV['APP_ENV']) {
            $this->addSql('DELETE supplier_rankings_files.* FROM supplier_rankings_files
INNER JOIN supplier_rankings ON supplier_rankings.id = supplier_rankings_files.supplier_ranking_id
WHERE supplier_rankings.supplier_id IS NULL');
            $this->addSql('DELETE FROM supplier_rankings WHERE supplier_id IS NULL');
            $this->addSql('DELETE FROM supplier_rankings WHERE id IN (SELECT id FROM supplier_rankings GROUP BY supplier_id HAVING count(supplier_id) > 1)');
        }

        $this->addSql('ALTER TABLE supplier_rankings DROP INDEX IDX_10D4CD672ADD6D8C, ADD UNIQUE INDEX UNIQ_10D4CD672ADD6D8C (supplier_id)');
        $this->addSql('ALTER TABLE supplier_rankings DROP FOREIGN KEY FK_10D4CD6764D218E');
        $this->addSql('ALTER TABLE supplier_rankings DROP FOREIGN KEY FK_10D4CD6738248176');
        $this->addSql('ALTER TABLE supplier_rankings DROP FOREIGN KEY FK_10D4CD676C755722');
        $this->addSql('ALTER TABLE supplier_rankings DROP FOREIGN KEY FK_10D4CD6798DEF399');
        $this->addSql('DROP INDEX IDX_10D4CD6738248176 ON supplier_rankings');
        $this->addSql('DROP INDEX IDX_10D4CD6764D218E ON supplier_rankings');
        $this->addSql('DROP INDEX IDX_10D4CD676C755722 ON supplier_rankings');
        $this->addSql('DROP INDEX IDX_10D4CD6798DEF399 ON supplier_rankings');
        $this->addSql('ALTER TABLE supplier_rankings DROP supplier_country_id, DROP location_id, DROP supplier_number, DROP supplier_name, DROP buyer_id, DROP currency_id, CHANGE supplier_id supplier_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplier_rankings DROP INDEX UNIQ_10D4CD672ADD6D8C, ADD INDEX IDX_10D4CD672ADD6D8C (supplier_id)');
        $this->addSql('ALTER TABLE supplier_rankings ADD supplier_country_id INT DEFAULT NULL, ADD location_id INT DEFAULT NULL, ADD supplier_number VARCHAR(255) DEFAULT NULL, ADD supplier_name VARCHAR(255) DEFAULT NULL, ADD buyer_id INT DEFAULT NULL, ADD currency_id INT DEFAULT NULL, CHANGE supplier_id supplier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supplier_rankings ADD CONSTRAINT FK_10D4CD6764D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE supplier_rankings ADD CONSTRAINT FK_10D4CD6738248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('ALTER TABLE supplier_rankings ADD CONSTRAINT FK_10D4CD676C755722 FOREIGN KEY (buyer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE supplier_rankings ADD CONSTRAINT FK_10D4CD6798DEF399 FOREIGN KEY (supplier_country_id) REFERENCES countries (id)');
        $this->addSql('CREATE INDEX IDX_10D4CD6738248176 ON supplier_rankings (currency_id)');
        $this->addSql('CREATE INDEX IDX_10D4CD6764D218E ON supplier_rankings (location_id)');
        $this->addSql('CREATE INDEX IDX_10D4CD676C755722 ON supplier_rankings (buyer_id)');
        $this->addSql('CREATE INDEX IDX_10D4CD6798DEF399 ON supplier_rankings (supplier_country_id)');
    }
}
