<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707120139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC - change unique CSR by TOC to multiple done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_service_record DROP INDEX UNIQ_3C9EAE6DEC02A7D0, ADD INDEX IDX_3C9EAE6DEC02A7D0 (technician_on_call_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_service_record DROP INDEX IDX_3C9EAE6DEC02A7D0, ADD UNIQUE INDEX UNIQ_3C9EAE6DEC02A7D0 (technician_on_call_id)');
    }
}
