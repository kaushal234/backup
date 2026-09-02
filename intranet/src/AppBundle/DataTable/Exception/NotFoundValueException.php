<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Exception;

use Kreyu\Bundle\DataTableBundle\Exception\ExceptionInterface;

class NotFoundValueException extends \UnexpectedValueException implements ExceptionInterface
{
}
