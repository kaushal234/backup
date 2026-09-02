<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251029122813 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add file to SPR';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE spare_parts_request_file (id INT NOT NULL, spare_parts_request_id INT DEFAULT NULL, INDEX IDX_39CC663D78E718A (spare_parts_request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE spare_parts_request_file ADD CONSTRAINT FK_39CC663D78E718A FOREIGN KEY (spare_parts_request_id) REFERENCES spare_parts_requests (id)');
        $this->addSql('ALTER TABLE spare_parts_request_file ADD CONSTRAINT FK_39CC663BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');

        $this->insertFeatureGroup('FEATURE_SPARE_PARTS_REQUESTS_FILE_UPLOAD', ['superuser', 'GG_PARTS', 'GG_SERVICE', 'GG_PARTS_AGENTS', 'GG_SERVICE_AGENTS']);
        $this->insertFeatureGroup('FEATURE_SPARE_PARTS_REQUESTS_FILE_DELETE', ['superuser', 'GG_PARTS', 'GG_SERVICE', 'GG_PARTS_AGENTS', 'GG_SERVICE_AGENTS']);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE spare_parts_request_file DROP FOREIGN KEY FK_39CC663D78E718A');
        $this->addSql('ALTER TABLE spare_parts_request_file DROP FOREIGN KEY FK_39CC663BF396750');
        $this->addSql('DROP TABLE spare_parts_request_file');
    }
}
