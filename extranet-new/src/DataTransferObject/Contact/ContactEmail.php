<?php

declare(strict_types=1);

namespace App\DataTransferObject\Contact;

use Symfony\Component\Validator\Constraints as Assert;

class ContactEmail
{
    /** @var string */
    public const SALES = 'Sales';

    /** @var string */
    public const SERVICE = 'Service';

    /** @var string */
    public const SPARE_PARTS = 'Spare Parts';

    #[Assert\NotBlank(message: 'extranet.error.message')]
    public string $message;

    #[Assert\Choice(choices: [self::SALES, self::SERVICE, self::SPARE_PARTS])]
    public string $department;
}
