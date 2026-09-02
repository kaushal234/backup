<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250924135835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create contracts entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, owner_id INT NOT NULL, currency_id INT NOT NULL, sub_category_id INT NOT NULL, parent_contract_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, signature_date DATETIME NOT NULL, expiration_date DATETIME DEFAULT NULL, indefinite_period_type TINYINT(1) NOT NULL, renewal_period INT NOT NULL, renewal_unit VARCHAR(255) NOT NULL, external_party VARCHAR(255) NOT NULL, internal_party JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', value INT NOT NULL, jurisdiction VARCHAR(255) NOT NULL, other_party_signatories JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_E98F2859B03A8386 (created_by_id), INDEX IDX_E98F28597E3C61F9 (owner_id), INDEX IDX_E98F285938248176 (currency_id), INDEX IDX_E98F2859F7BFE87C (sub_category_id), INDEX IDX_E98F28594E5AF28D (parent_contract_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract_siblings (contract_id INT NOT NULL, contract_sibling_id INT NOT NULL, INDEX IDX_324DEC8B2576E0FD (contract_id), INDEX IDX_324DEC8BFFA5054A (contract_sibling_id), PRIMARY KEY(contract_id, contract_sibling_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract_people (contract_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_14568F6B2576E0FD (contract_id), INDEX IDX_14568F6B3147C936 (people_id), PRIMARY KEY(contract_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract_files (id INT NOT NULL, contract_id INT DEFAULT NULL, INDEX IDX_4534DC7D2576E0FD (contract_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sub_category (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_BCE3F79812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F28597E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F285938248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id)');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F28594E5AF28D FOREIGN KEY (parent_contract_id) REFERENCES contract (id)');
        $this->addSql('ALTER TABLE contract_siblings ADD CONSTRAINT FK_324DEC8B2576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id)');
        $this->addSql('ALTER TABLE contract_siblings ADD CONSTRAINT FK_324DEC8BFFA5054A FOREIGN KEY (contract_sibling_id) REFERENCES contract (id)');
        $this->addSql('ALTER TABLE contract_people ADD CONSTRAINT FK_14568F6B2576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_people ADD CONSTRAINT FK_14568F6B3147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_files ADD CONSTRAINT FK_4534DC7D2576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id)');
        $this->addSql('ALTER TABLE contract_files ADD CONSTRAINT FK_4534DC7DBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sub_category ADD CONSTRAINT FK_BCE3F79812469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');

        $this->addSql("INSERT INTO category (id, name) VALUES
        (1, 'INSURANCES'),
        (2, 'HUMAN RESSOURCES'),
        (3, 'REAL ESTATE'),
        (4, 'M&A'),
        (5, 'CUSTOMERS'),
        (6, 'IP/IT'),
        (7, 'VENDORS'),
        (8, 'BANK'),
        (9, 'INTERCO')
    ");

        $this->addSql("INSERT INTO sub_category (name, category_id) VALUES
        ('BUILDING - PROPERTY DAMAGES (master policy)', 1),
        ('CAR (local policies)', 1),
        ('D&O (master + local policies)', 1),
        ('GENERAL LIABILITY (master + local)', 1),
        ('AERO LIABILITY (master + LOCAL)', 1),
        ('WORKER COMPENSATION (local policies)', 1),

        ('COLLECTIVE BARGAINING AGREEMENT', 2),
        ('EMPLOYEE HANDBOOK/INTERNAL REGULATION', 2),
        ('PROFIT SHARING AGREEMENT - INCENTIVE SCHEMES', 2),
        ('WORKING HOURS AGREEMENT', 2),
        ('INDIVIDUAL EMPLOYMENT CONTRACT/EMPLOYMENT OFFER LETTERS', 2),
        ('MANPOWER AGREEMENT - INTERIM AGREEMENT', 2),
        ('HEALTH INSURANCE (MUTUELLE) & BENEFITS (Prévoyance)', 2),
        ('NON-COMPETE AGREEMENT', 2),
        ('SETTLEMENT AGREEMENT WITH EMPLOYEES', 2),
        ('NON DISCLOSURE AND INTELLECTUAL PROPERTY AGREEMENT WITH EMPLOYEES', 2),

        ('LAND & BUILDING ACQUISITION CONTRACT', 3),
        ('OPTION TO PURCHASE', 3),
        ('BUILDING LEASING & RENTAL CONTRACT', 3),
        ('LAND & BUILDING DIVESTURE CONTRACT', 3),

        ('LOI', 4),
        ('PUT AGREEMENT', 4),
        ('SALES&PURCHASE AGREEMENT', 4),
        ('JOINT VENTURE AGREEMENT', 4),

        ('FRAMEWORK AGREEMENT/MASTER PURCHASE AGREEMENT (for sellings goods)', 5),
        ('SALE AGREEMENT/PO/EQUOTE/SUPPLY AGREEMENT (for the supply of goods)', 5),
        ('GOVERNMENT PROCUREMENT AGREEMENT/TENDER', 5),
        ('MAINTENANCE CONTRACT', 5),
        ('SERVICE CONTRACT - SLA', 5),
        ('DEMO CONTRACT', 5),
        ('LEASING CONTRACT', 5),
        ('THIRD PARTY FRAMEWORK CONTRACT', 5),
        ('NDA', 5),

        ('LICENSING AGREEMENT (for licensing intellectual property) & software license AGREEMENT (for licensing software)', 6),
        ('IT SERVICES AGREEMENT (for IT service provision)', 6),

        ('NDA', 7),
        ('PURCHASE AGREEMENT (for buying goods)', 7),
        ('CONSULTING AGREEMENT', 7),
        ('VENDOR FRAMEWORK AGREEMENT', 7),
        ('INDEPENDENT CONTRACTOR/FREELANCE CONTRACT', 7),
        ('SERVICE CONTRACT', 7),
        ('PARTNERSHIP CONTRACT', 7),

        ('BANK FRAMEWORK CONTRACT', 8),
        ('BORROWING CONTRACT  - LOAN AGREEMENT', 8),
        ('SECURITIES CONTRACT (pledge, mortgage, guarantee)', 8),
        ('FINANCIAL LEASE', 8),

        ('CASH POOLING CONTRACT', 9),
        ('MANAGEMENT FEES AGREEMENT', 9),
        ('PURCHASE & SALES CONTRACT', 9),
        ('TRANSFER PRICE AGREEMENT', 9),
        ('SERVICE AGREEMENT', 9),
        ('PARENT COMPANY GUARANTEE', 9)
    ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859B03A8386');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F28597E3C61F9');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F285938248176');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859F7BFE87C');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F28594E5AF28D');
        $this->addSql('ALTER TABLE contract_siblings DROP FOREIGN KEY FK_324DEC8B2576E0FD');
        $this->addSql('ALTER TABLE contract_siblings DROP FOREIGN KEY FK_324DEC8BFFA5054A');
        $this->addSql('ALTER TABLE contract_people DROP FOREIGN KEY FK_14568F6B2576E0FD');
        $this->addSql('ALTER TABLE contract_people DROP FOREIGN KEY FK_14568F6B3147C936');
        $this->addSql('ALTER TABLE contract_files DROP FOREIGN KEY FK_4534DC7D2576E0FD');
        $this->addSql('ALTER TABLE contract_files DROP FOREIGN KEY FK_4534DC7DBF396750');
        $this->addSql('ALTER TABLE sub_category DROP FOREIGN KEY FK_BCE3F79812469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE contract_siblings');
        $this->addSql('DROP TABLE contract_people');
        $this->addSql('DROP TABLE contract_files');
        $this->addSql('DROP TABLE sub_category');
    }
}
