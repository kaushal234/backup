<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250820092159 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add pictograms and their categories and features.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pictograms (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, description VARCHAR(255) NOT NULL, INDEX IDX_E08C63CF12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pictograms_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, color VARCHAR(7) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pictograms_files (id INT NOT NULL, pictogram_id INT DEFAULT NULL, main TINYINT(1) NOT NULL, INDEX IDX_265A4A616B7C33B (pictogram_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pictograms ADD CONSTRAINT FK_E08C63CF12469DE2 FOREIGN KEY (category_id) REFERENCES pictograms_categories (id)');
        $this->addSql('ALTER TABLE pictograms_files ADD CONSTRAINT FK_265A4A616B7C33B FOREIGN KEY (pictogram_id) REFERENCES pictograms (id)');
        $this->addSql('ALTER TABLE pictograms_files ADD CONSTRAINT FK_265A4A6BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PICTOGRAM_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PICTOGRAM_READ")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PICTOGRAM_UPDATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PICTOGRAM_DELETE")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PICTOGRAM_CATEGORY_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PICTOGRAM_CATEGORY_READ")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PICTOGRAM_CATEGORY_UPDATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PICTOGRAM_CATEGORY_DELETE")');

        $this->insertFeatureGroup('FEATURE_PICTOGRAM_READ', ['ACL_AUTH_INTRANET']);
        $this->insertFeatureGroup('FEATURE_PICTOGRAM_CATEGORY_READ', ['ACL_AUTH_INTRANET']);

        $this->insertFeatureGroup('FEATURE_PICTOGRAM_CREATE', ['SUPERUSER', 'role_GCTO']);
        $this->insertFeatureGroup('FEATURE_PICTOGRAM_UPDATE', ['SUPERUSER', 'role_GCTO']);
        $this->insertFeatureGroup('FEATURE_PICTOGRAM_DELETE', ['SUPERUSER', 'role_GCTO']);

        $this->insertFeatureGroup('FEATURE_PICTOGRAM_CATEGORY_CREATE', ['SUPERUSER', 'role_GCTO']);
        $this->insertFeatureGroup('FEATURE_PICTOGRAM_CATEGORY_UPDATE', ['SUPERUSER', 'role_GCTO']);
        $this->insertFeatureGroup('FEATURE_PICTOGRAM_CATEGORY_DELETE', ['SUPERUSER', 'role_GCTO']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pictograms DROP FOREIGN KEY FK_E08C63CF12469DE2');
        $this->addSql('ALTER TABLE pictograms_files DROP FOREIGN KEY FK_265A4A616B7C33B');
        $this->addSql('ALTER TABLE pictograms_files DROP FOREIGN KEY FK_265A4A6BF396750');
        $this->addSql('DROP TABLE pictograms');
        $this->addSql('DROP TABLE pictograms_categories');
        $this->addSql('DROP TABLE pictograms_files');
    }
}
