<?php

declare(strict_types=1);

namespace App\Postman\EventListener;

use App\Postman\Event\NodeEvent;
use App\Postman\Resource\JsonBody;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;

#[AsEventListener(event: NodeEvent::class, method: 'addBody')]
class TokenBodyListener
{
    public function addBody(NodeEvent $event): void
    {
        if (Request::METHOD_POST !== $event->method) {
            return;
        }

        if ('/token' !== $event->route->getPath()) {
            return;
        }

        $jsonBody = new JsonBody();
        $jsonBody->mode = 'formdata';
        $jsonBody->options = [];
        $jsonBody->formdata = [
            ['key' => 'username', 'value' => 'user-basic@tld.fr', 'type' => 'text'],
            ['key' => 'password', 'value' => 'P@ssw0rd15chars', 'type' => 'text'],
            ['key' => 'portal', 'value' => 'intranet', 'type' => 'text'],
        ];

        $event->node->request->body = $jsonBody;
    }
}
