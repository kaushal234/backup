<?php

declare(strict_types=1);

namespace App\Javelo\DataTransformer;

use App\Javelo\Resources\User;
use Symfony\Component\Form\DataTransformerInterface;

class UserPatchDataTransformer implements DataTransformerInterface
{
    public function transform($value): ?array
    {
        if (!$value instanceof User) {
            return null;
        }

        // Required SCIM schema https://api.javelo.io/public/doc/api/scim#tag/Users/paths/~1scim~1v2~1Users~1%7Bid%7D/patch
        return [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
            'Operations' => [
                ['op' => 'Replace', 'path' => 'title', 'value' => $value->title],
                ['op' => 'Replace', 'path' => 'userName', 'value' => $value->userName],
                ['op' => 'Replace', 'path' => 'name.familyName', 'value' => $value->familyName],
                ['op' => 'Replace', 'path' => 'name.givenName', 'value' => $value->givenName],
                ['op' => 'Replace', 'path' => 'locale', 'value' => $value->locale],
                ['op' => 'Replace', 'path' => 'active', 'value' => $value->active],
                ['op' => 'Replace', 'path' => 'externalId', 'value' => $value->externalId],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User:department', 'value' => $value->department],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:intranetId', 'value' => $value->intranetId],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:managerUserName', 'value' => $value->managerUserName],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:businessUnit', 'value' => $value->businessUnit],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:region', 'value' => $value->region],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:subdivision', 'value' => $value->subdivision],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:division', 'value' => $value->division],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:gender', 'value' => $value->gender],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:contractType', 'value' => $value->contractType],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:lastExitDate', 'value' => $value->getLastExitDate()],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:position', 'value' => $value->position],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:workingTime', 'value' => $value->workingTime],
            ],
        ];
    }

    public function reverseTransform($value): mixed
    {
        throw new \Exception('Not implemented');
    }
}
