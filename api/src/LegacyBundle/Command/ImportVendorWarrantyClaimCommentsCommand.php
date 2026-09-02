<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Purchasing\VendorWarrantyClaim;
use LegacyBundle\Command\Helper\CommentImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:purchasing:vwc_comments')]
class ImportVendorWarrantyClaimCommentsCommand extends Command
{
    private readonly CommentImportHelper $commentImportHelper;

    public function __construct(CommentImportHelper $commentImportHelper)
    {
        parent::__construct();
        $this->setDescription('Import VWC comments from legacy');
        $this->commentImportHelper = $commentImportHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->commentImportHelper->progressiveImport($output, 'VWC', VendorWarrantyClaim::class, 'vwc', 'id');

        return 0;
    }
}
