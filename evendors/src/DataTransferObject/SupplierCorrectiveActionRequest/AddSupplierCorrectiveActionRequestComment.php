<?php

declare(strict_types=1);

namespace App\DataTransferObject\SupplierCorrectiveActionRequest;

use Symfony\Component\Validator\Constraints as Assert;

final class AddSupplierCorrectiveActionRequestComment
{
    #[Assert\NotBlank(message: 'supplier_corrective_action_request.comment.add.message.blank')]
    public string $message;

    #[Assert\File(uploadErrorMessage: 'supplier_corrective_action_request.comment.add.file.invalid')]
    public ?string $file = null;
}
