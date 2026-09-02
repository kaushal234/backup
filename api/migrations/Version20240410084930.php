<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240410084930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Local Key Users to modules';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE module_people (module_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_82152A09AFC2B591 (module_id), INDEX IDX_82152A093147C936 (people_id), PRIMARY KEY(module_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE module_people ADD CONSTRAINT FK_82152A09AFC2B591 FOREIGN KEY (module_id) REFERENCES modules (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE module_people ADD CONSTRAINT FK_82152A093147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE module_people DROP FOREIGN KEY FK_82152A09AFC2B591');
        $this->addSql('ALTER TABLE module_people DROP FOREIGN KEY FK_82152A093147C936');
        $this->addSql('DROP TABLE module_people');
    }
}
