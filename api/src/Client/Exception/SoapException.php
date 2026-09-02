<?php

declare(strict_types=1);

namespace App\Client\Exception;

use App\Client\Response\Message;

class SoapException extends \Exception
{
    private readonly string $resource;

    /**
     * @var Message[]
     */
    private array $messages = [];

    private function __construct(string $resource, string $message, ?\SoapFault $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->resource = $resource;
    }

    public function addMessage(Message $message): self
    {
        $this->messages[] = $message;

        return $this;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public static function createFromSoapFault(string $resource, \SoapFault $soapFault): self
    {
        if (null === $soapFault->detail) {
            return new self($resource, $soapFault->getMessage(), $soapFault);
        }

        $detail = $soapFault->detail;
        $result = isset($detail->Result) ? (array) $detail->Result : ($detail['Result'] ?? []);

        $message = $result['messageText'] ?? 'Something went wrong while requesting external API';
        $error = new self($resource, $message, $soapFault);
        $error->addMessage(new Message(Message::ERROR, $message));

        $messageDetail = (array) ($result['MessageDetails'] ?? []);
        $messages = (array) ($messageDetail['Message'] ?? []);

        foreach ($messages as $messageDetail) {
            $messageDetail = (array) $messageDetail;
            $error->addMessage(new Message($messageDetail['messageType'] ?? '', $messageDetail['messageText'] ?? ''));
        }

        return $error;
    }
}
