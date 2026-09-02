<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;
use Psl\Vec;

enum RequestForQuotationStatus: string
{
    case created = 'created';
    case sent = 'sent';
    case modified = 'modified';
    case responded = 'responded';
    case inProcess = 'in.process';
    case noBid = 'no.bid';
    case noResponse = 'no.response';
    case negotiating = 'negotiating';
    case accepted = 'accepted';
    case rejected = 'rejected';
    case processed = 'processed';

    /**
     * @return Type\TypeInterface<non-empty-string>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\union(...Vec\map(
            self::cases(),
            /**
             * @return Type\TypeInterface<non-empty-string>
             */
            static fn (RequestForQuotationStatus $status): Type\TypeInterface => Type\literal_scalar($status->value),
        ));
    }
}
