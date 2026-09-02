<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200116163000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create features for baan archive download and assign it to user groups';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT IGNORE INTO feature (name) VALUES('FEATURE_STRS_PDF_ARCHIVE_DOWNLOAD')");
        $this->addSql("INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = 'FEATURE_STRS_PDF_ARCHIVE_DOWNLOAD'
			AND user_group.name IN (
			    'SUPERUSER',
                'gg_ADMIN',
                'gg_PUR',
                'gg_SALES',
                'gg_PARTS',
                'gg_SUPPORT',
                'gg_ACCT',
                'role_COO'
			)"
        );
        $this->addSql("INSERT IGNORE INTO feature (name) VALUES('FEATURE_INVOICE_ARCHIVE_DOWNLOAD')");
        $this->addSql("INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = 'FEATURE_INVOICE_ARCHIVE_DOWNLOAD'
			AND user_group.name IN (
			    'role_AP',
			    'gg_ACCT',
			    'seq_acct.japan.invoice.approval.step0',
			    'seq_acct.japan.invoice.approval.step1',
			    'seq_acct.japan.invoice.approval.evp',
			    'seq_acct.japan.invoice.approval.coo',
			    'seq_acct.japan.invoice.approval.ap',
			    'seq_acct.japan.invoice.approval.ceo',
			    'seq_acct.invoice.approval_step1',
			    'role_COO',
			    'role_CEO',
			    'superuser',
			    'acl_doc_NonPOInvoice.approval'
			)"
        );
        $this->addSql("INSERT IGNORE INTO feature (name) VALUES('FEATURE_INVOICE_PO_SPOOL_DOWNLOAD')");
        $this->addSql("INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = 'FEATURE_INVOICE_PO_SPOOL_DOWNLOAD'
			AND user_group.name in (
                'role_CFO',
                'gg_ACCT',
                'gg_PUR',
                'role_MLM',
                'superuser'
			)"
        );
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_ARCHIVE_DOWNLOAD")');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_ARCHIVE_DOWNLOAD"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
