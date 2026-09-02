<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211110160911 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE extranet_user_profile_phone');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE extranet_user_profile_phone (extranet_user_profile_id INT NOT NULL, phone_id INT NOT NULL, INDEX IDX_34BB37783B7323CB (phone_id), INDEX IDX_34BB3778B56089BF (extranet_user_profile_id), PRIMARY KEY(extranet_user_profile_id, phone_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE extranet_user_profile_phone ADD CONSTRAINT FK_34BB37783B7323CB FOREIGN KEY (phone_id) REFERENCES phone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE extranet_user_profile_phone ADD CONSTRAINT FK_34BB3778B56089BF FOREIGN KEY (extranet_user_profile_id) REFERENCES extranet_user_profile (id) ON DELETE CASCADE');
    }
}
