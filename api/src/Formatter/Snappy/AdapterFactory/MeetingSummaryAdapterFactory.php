<?php

declare(strict_types=1);

namespace App\Formatter\Snappy\AdapterFactory;

use App\Entity\MinutesOfMeeting\Meeting;
use App\Formatter\Snappy\Adapter;

class MeetingSummaryAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @var string
     */
    final public const PURPOSE = 'meeting_summary';

    /**
     * {@inheritdoc}
     */
    public function getAdapter($meeting, string $format): Adapter
    {
        $adapter = new Adapter(
            'Pdf/MinutesOfMeeting/Meeting/layout.html.twig',
            ['meeting' => $meeting]
        );
        $adapter->setHeaderTemplate('Pdf/MinutesOfMeeting/Meeting/header.html.twig');
        $adapter->setFooterTemplate('Pdf/MinutesOfMeeting/Meeting/footer.html.twig');

        return $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, string $format): bool
    {
        return $object instanceof Meeting && 'pdf' === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getPurpose(): string
    {
        return self::PURPOSE;
    }
}
