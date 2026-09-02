<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221130211557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create join table between features and subdivisions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE feature_sub_division (feature_id INT NOT NULL, sub_division_id INT NOT NULL, INDEX IDX_12DD1FDE60E4B879 (feature_id), INDEX IDX_12DD1FDEA47CE717 (sub_division_id), PRIMARY KEY(feature_id, sub_division_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE feature_sub_division ADD CONSTRAINT FK_12DD1FDE60E4B879 FOREIGN KEY (feature_id) REFERENCES feature (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feature_sub_division ADD CONSTRAINT FK_12DD1FDEA47CE717 FOREIGN KEY (sub_division_id) REFERENCES directory_sub_division (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PARTS_DASHBOARD_FULL_VIEW")');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feature_sub_division DROP FOREIGN KEY FK_12DD1FDE60E4B879');
        $this->addSql('ALTER TABLE feature_sub_division DROP FOREIGN KEY FK_12DD1FDEA47CE717');
        $this->addSql('DROP TABLE feature_sub_division');
    }
}
