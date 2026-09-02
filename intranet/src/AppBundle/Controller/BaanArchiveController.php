<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BaanArchiveController extends AbstractController
{
    final public const STRS_ARCHIVE_PATH = '/mnt/grpfps10.strs_pdf/ARCHIVE/';
    final public const INVOICES_ARCHIVE_PATH = '/mnt/grpfps10.online_approvals/invoices/ARCHIVE/';
    final public const INVOICES_PO_SPOOL_PATH = '/mnt/grpfps10.online_approvals/invoices_po/spool/';

    #[Route(path: 'strs_pdf/archive/{path}', name: 'strs_pdf_archive_file_download', methods: 'GET', requirements: ['path' => '.+'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_ARCHIVE_DOWNLOAD') or is_granted('FEATURE_STRS_PDF_ARCHIVE_DOWNLOAD')"))]
    public function downloadStrsPdfArchive($path)
    {
        try {
            return new BinaryFileResponse(self::STRS_ARCHIVE_PATH.$path);
        } catch (\Exception $e) {
            throw $this->createNotFoundException('Not Found', $e);
        }
    }

    #[Route(path: 'invoices/archive/{path}', name: 'invoices_archive_file_download', methods: 'GET', requirements: ['path' => '.+'])]
    #[IsGranted('FEATURE_INVOICE_ARCHIVE_DOWNLOAD')]
    public function downloadInvoicesArchive($path)
    {
        try {
            return new BinaryFileResponse(self::INVOICES_ARCHIVE_PATH.$path);
        } catch (\Exception $e) {
            throw $this->createNotFoundException('Not Found', $e);
        }
    }

    #[Route(path: 'invoices_po/spool/{path}', name: 'invoices_po_spool_file_download', methods: 'GET', requirements: ['path' => '.+'])]
    #[IsGranted('FEATURE_INVOICE_PO_SPOOL_DOWNLOAD')]
    public function downloadInvoicesPoSpool($path)
    {
        try {
            return new BinaryFileResponse(self::INVOICES_PO_SPOOL_PATH.$path);
        } catch (\Exception $e) {
            throw $this->createNotFoundException('Not Found', $e);
        }
    }
}
