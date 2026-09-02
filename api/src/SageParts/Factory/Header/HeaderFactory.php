<?php

declare(strict_types=1);

namespace App\SageParts\Factory\Header;

use App\SageParts\Models\Header\Header;

class HeaderFactory
{
    private FromFactory $fromFactory;
    private ToFactory $toFactory;
    private SenderFactory $senderFactory;

    public function __construct(FromFactory $fromFactory, ToFactory $toFactory, SenderFactory $senderFactory)
    {
        $this->fromFactory = $fromFactory;
        $this->toFactory = $toFactory;
        $this->senderFactory = $senderFactory;
    }

    public function create(): Header
    {
        $header = new Header();
        $header
            ->setFrom($this->fromFactory->create())
            ->setTo($this->toFactory->create())
            ->setSender($this->senderFactory->create())
        ;

        return $header;
    }
}
