<?php

declare(strict_types=1);

namespace App\Controller\MinutesOfMeeting;

use App\Entity\MinutesOfMeeting\Meeting;
use App\Formatter\Snappy\AdapterFactory\MeetingSummaryAdapterFactory;
use App\Formatter\Snappy\FormatterInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

class MeetingSummaryPdfController
{
    private readonly FormatterInterface $pdfFormatter;

    public function __construct(FormatterInterface $pdfFormatter)
    {
        $this->pdfFormatter = $pdfFormatter;
    }

    public function __invoke(Meeting $meeting)
    {
        $content = $this->pdfFormatter->convert($meeting, MeetingSummaryAdapterFactory::PURPOSE, 'pdf');

        return new PdfResponse($content, \sprintf('meeting-%07s.pdf', $meeting->getId()), 'application/pdf', 'inline');
    }
}
