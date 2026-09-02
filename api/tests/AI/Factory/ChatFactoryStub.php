<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory;

use App\AI\Factory\ChatFactoryInterface;
use Symfony\AI\Agent\MockAgent;
use Symfony\AI\Chat\Chat;
use Symfony\AI\Chat\ChatInterface;
use Symfony\AI\Chat\InMemory\Store;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\StreamResult;

class ChatFactoryStub implements ChatFactoryInterface
{
    public function createChat(string $iri): ChatInterface
    {
        $agent = new MockAgent([
            'Hello' => self::streamOf('Hi there! How can I help?'),
            "Summarize this file[Attached file content]\n6 Interfacing databases 20\n6.1 Structured Query Language SQL 20\n6.1.1 SELECT command . 20\n6.1.2 INSERT command 22\n6.1.3 UPDATE command 22\n6.2 Scenario: MySQL database 22\n6.2.1 Creating a database and a database user 23\n6.2.2 Extracting information from the database with PHP 25\n6.2.3 Creating new records with PHP\n26\n6.2.4 Updating records with PHP\n27\n6.2.5 Finishing the application\n29\n6.3 Scenario: Access database on Microsoft Windows 2000 Server 30\n6.3.1 Extracting information from the Microsoft Access database with PHP 31\n6.3.2 Creating new records with PHP 32\n6.3.3 Updating records with PHP 33\n6.3.4 Finishing the application 35" => self::streamOf('Thank you'),
        ]);

        return new Chat($agent, new Store());
    }

    private static function streamOf(string $text): StreamResult
    {
        return new StreamResult((static function () use ($text): \Generator {
            yield new TextDelta($text);
        })());
    }
}
