<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210914200713 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE directory_location ADD contact_parts_customer_support_email VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE directory_location SET contact_parts_customer_support_email =  contact_spare_parts_email ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE directory_location DROP contact_parts_customer_support_email');
    }
}
