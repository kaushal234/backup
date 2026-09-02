<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251008144240 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add DMS Restrictions';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dms_restrictions (id INT AUTO_INCREMENT NOT NULL, business_unit_id INT DEFAULT NULL, region_id INT DEFAULT NULL, division_id INT DEFAULT NULL, sub_division_id INT DEFAULT NULL, position_id INT DEFAULT NULL, department_id INT DEFAULT NULL, dms_id INT NOT NULL, legacy_id INT NOT NULL, INDEX IDX_1B72B628A58ECB40 (business_unit_id), INDEX IDX_1B72B62898260155 (region_id), INDEX IDX_1B72B62841859289 (division_id), INDEX IDX_1B72B628A47CE717 (sub_division_id), INDEX IDX_1B72B628DD842E46 (position_id), INDEX IDX_1B72B628AE80F5DF (department_id), INDEX IDX_1B72B628A38F4C43 (dms_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dms_restrictions ADD CONSTRAINT FK_1B72B628A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id)');
        $this->addSql('ALTER TABLE dms_restrictions ADD CONSTRAINT FK_1B72B62898260155 FOREIGN KEY (region_id) REFERENCES directory_region (id)');
        $this->addSql('ALTER TABLE dms_restrictions ADD CONSTRAINT FK_1B72B62841859289 FOREIGN KEY (division_id) REFERENCES directory_division (id)');
        $this->addSql('ALTER TABLE dms_restrictions ADD CONSTRAINT FK_1B72B628A47CE717 FOREIGN KEY (sub_division_id) REFERENCES directory_sub_division (id)');
        $this->addSql('ALTER TABLE dms_restrictions ADD CONSTRAINT FK_1B72B628DD842E46 FOREIGN KEY (position_id) REFERENCES directory_position (id)');
        $this->addSql('ALTER TABLE dms_restrictions ADD CONSTRAINT FK_1B72B628AE80F5DF FOREIGN KEY (department_id) REFERENCES directory_department (id)');
        $this->addSql('ALTER TABLE dms_restrictions ADD CONSTRAINT FK_1B72B628A38F4C43 FOREIGN KEY (dms_id) REFERENCES dms (id)');

        $this->insertFeatureGroup('FEATURE_DMS_VIEW_ALL', ['SUPERUSER']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dms_restrictions DROP FOREIGN KEY FK_1B72B628A58ECB40');
        $this->addSql('ALTER TABLE dms_restrictions DROP FOREIGN KEY FK_1B72B62898260155');
        $this->addSql('ALTER TABLE dms_restrictions DROP FOREIGN KEY FK_1B72B62841859289');
        $this->addSql('ALTER TABLE dms_restrictions DROP FOREIGN KEY FK_1B72B628A47CE717');
        $this->addSql('ALTER TABLE dms_restrictions DROP FOREIGN KEY FK_1B72B628DD842E46');
        $this->addSql('ALTER TABLE dms_restrictions DROP FOREIGN KEY FK_1B72B628AE80F5DF');
        $this->addSql('ALTER TABLE dms_restrictions DROP FOREIGN KEY FK_1B72B628A38F4C43');
        $this->addSql('DROP TABLE dms_restrictions');
    }
}
