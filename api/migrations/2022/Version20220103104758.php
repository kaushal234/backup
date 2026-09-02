<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220103104758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create entity ManualPrint and create feature';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE manual_prints (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', manual_id INT DEFAULT NULL, manual_printer_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, standard INT NOT NULL, full INT NOT NULL, extra INT NOT NULL, chapter5 INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, requested_delivery_date DATETIME DEFAULT NULL, downloaded_at DATETIME DEFAULT NULL, legacy_id INT NOT NULL, INDEX IDX_F057EAD39BA073D6 (manual_id), INDEX IDX_F057EAD34E01B385 (manual_printer_id), INDEX IDX_F057EAD3DE12AB56 (created_by), INDEX IDX_F057EAD316FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE manual_prints ADD CONSTRAINT FK_F057EAD39BA073D6 FOREIGN KEY (manual_id) REFERENCES manuals (id)');
        $this->addSql('ALTER TABLE manual_prints ADD CONSTRAINT FK_F057EAD34E01B385 FOREIGN KEY (manual_printer_id) REFERENCES manual_printers (id)');
        $this->addSql('ALTER TABLE manual_prints ADD CONSTRAINT FK_F057EAD3DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE manual_prints ADD CONSTRAINT FK_F057EAD316FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE manual_prints');
    }
}
