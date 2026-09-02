<?php

declare(strict_types=1);

namespace App\DataTransferObject\TechnicianOnCall;

use Symfony\Component\Validator\Constraints as Assert;

class AddTechnicianOnCallComment
{
    #[Assert\NotBlank(message: 'supplier_corrective_action_request.comment.add.message.blank')]
    public string $message;

    public ?string $file = null;
}
