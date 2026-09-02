<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930063134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update contract entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contract_business_unit (contract_id INT NOT NULL, business_unit_id INT NOT NULL, INDEX IDX_D7B8F862576E0FD (contract_id), INDEX IDX_D7B8F86A58ECB40 (business_unit_id), PRIMARY KEY(contract_id, business_unit_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract_region (contract_id INT NOT NULL, region_id INT NOT NULL, INDEX IDX_3322143B2576E0FD (contract_id), INDEX IDX_3322143B98260155 (region_id), PRIMARY KEY(contract_id, region_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract_division (contract_id INT NOT NULL, division_id INT NOT NULL, INDEX IDX_85A05CEB2576E0FD (contract_id), INDEX IDX_85A05CEB41859289 (division_id), PRIMARY KEY(contract_id, division_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract_premise (contract_id INT NOT NULL, premise_id INT NOT NULL, INDEX IDX_D9ED46F92576E0FD (contract_id), INDEX IDX_D9ED46F9BD8D5AD9 (premise_id), PRIMARY KEY(contract_id, premise_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract_business_unit ADD CONSTRAINT FK_D7B8F862576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_business_unit ADD CONSTRAINT FK_D7B8F86A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_region ADD CONSTRAINT FK_3322143B2576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_region ADD CONSTRAINT FK_3322143B98260155 FOREIGN KEY (region_id) REFERENCES directory_region (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_division ADD CONSTRAINT FK_85A05CEB2576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_division ADD CONSTRAINT FK_85A05CEB41859289 FOREIGN KEY (division_id) REFERENCES directory_division (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_premise ADD CONSTRAINT FK_D9ED46F92576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_premise ADD CONSTRAINT FK_D9ED46F9BD8D5AD9 FOREIGN KEY (premise_id) REFERENCES premises (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract CHANGE currency_id currency_id INT DEFAULT NULL, CHANGE renewal_period renewal_period INT DEFAULT NULL, CHANGE renewal_unit renewal_unit VARCHAR(255) DEFAULT NULL, CHANGE external_party external_party VARCHAR(255) DEFAULT NULL, CHANGE value value INT DEFAULT NULL, CHANGE jurisdiction jurisdiction VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contract_business_unit DROP FOREIGN KEY FK_D7B8F862576E0FD');
        $this->addSql('ALTER TABLE contract_business_unit DROP FOREIGN KEY FK_D7B8F86A58ECB40');
        $this->addSql('ALTER TABLE contract_region DROP FOREIGN KEY FK_3322143B2576E0FD');
        $this->addSql('ALTER TABLE contract_region DROP FOREIGN KEY FK_3322143B98260155');
        $this->addSql('ALTER TABLE contract_division DROP FOREIGN KEY FK_85A05CEB2576E0FD');
        $this->addSql('ALTER TABLE contract_division DROP FOREIGN KEY FK_85A05CEB41859289');
        $this->addSql('ALTER TABLE contract_premise DROP FOREIGN KEY FK_D9ED46F92576E0FD');
        $this->addSql('ALTER TABLE contract CHANGE currency_id currency_id INT NOT NULL, CHANGE renewal_period renewal_period INT NOT NULL, CHANGE renewal_unit renewal_unit VARCHAR(255) NOT NULL, CHANGE external_party external_party VARCHAR(255) NOT NULL, CHANGE value value INT NOT NULL, CHANGE jurisdiction jurisdiction VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE contract_premise DROP FOREIGN KEY FK_D9ED46F9BD8D5AD9');
        $this->addSql('DROP TABLE contract_business_unit');
        $this->addSql('DROP TABLE contract_region');
        $this->addSql('DROP TABLE contract_division');
        $this->addSql('DROP TABLE contract_premise');
    }
}
