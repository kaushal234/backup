<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203155954 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'TOC Survey done and cp';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE technician_on_call_survey (id INT AUTO_INCREMENT NOT NULL, technician_on_call_id INT NOT NULL, created_by_id INT DEFAULT NULL, execution INT NOT NULL, responsiveness INT NOT NULL, communication INT NOT NULL, attitude INT NOT NULL, comment LONGTEXT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1F7954F1EC02A7D0 (technician_on_call_id), INDEX IDX_1F7954F1B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE technician_on_call_survey ADD CONSTRAINT FK_1F7954F1EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('ALTER TABLE technician_on_call_survey ADD CONSTRAINT FK_1F7954F1B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->insertFeatureGroup('FEATURE_TECHNICIAN_ON_CALL_SURVEY', ['ROLE_CSM', 'ROLE_CSD']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call_survey DROP FOREIGN KEY FK_1F7954F1EC02A7D0');
        $this->addSql('ALTER TABLE technician_on_call_survey DROP FOREIGN KEY FK_1F7954F1B03A8386');
        $this->addSql('DROP TABLE technician_on_call_survey');
    }
}
