<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241017070347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'edit csr files discriminator';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE files set discr = 'customer_service_record_file' where discr = 'cutomer_service_record_file'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE files set discr = 'cutomer_service_record_file' where discr = 'customer_service_record_file'");
    }
}
