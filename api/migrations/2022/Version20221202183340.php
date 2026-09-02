<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221202183340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove process and responsible from NCR';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE non_conformity DROP FOREIGN KEY FK_9726A49A7EC2F574');
        $this->addSql('DROP INDEX IDX_9726A49A7EC2F574 ON non_conformity');
        $this->addSql('ALTER TABLE non_conformity DROP process_id, DROP responsible');
    }

    public function down(Schema $schema): void
    {
    }
}
