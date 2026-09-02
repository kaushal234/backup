<?php

declare(strict_types=1);

namespace App\Notifier\Service\TechnicianOnCall;

enum TechnicianOnCallMailSubject: string
{
    case TOC_CREATED_BY_CUSTOMER = 'toc_created_by_customer';
    case TOC_CREATED_BY_PEOPLE_INTERNAL = 'toc_created_by_people_internal';
    case TOC_CREATED_BY_PEOPLE_EXTERNAL = 'toc_created_by_people_external';
    case TOC_UPDATED = 'toc_updated';
    case TOC_PENDING_TO_IN_PROGRESS_INTERNAL = 'toc_pending_to_in_progress_internal';
    case TOC_PENDING_TO_IN_PROGRESS_EXTERNAL = 'toc_pending_to_in_progress_external';
    case TOC_NEW_COMMENT_INTERNAL = 'toc_new_comment_internal';
    case TOC_NEW_COMMENT_EXTERNAL = 'toc_new_comment_external';
    case TOC_NEW_COMMENT_BY_CUST = 'toc_new_comment_by_cust';
    case TOC_FACTORY_FLAG = 'toc_factory_flag';
    case TOC_SOLVED_EXTERNAL = 'toc_solved_external';
    case TOC_BLACK_CAT = 'toc_black_cat';
    case TOC_CSR_CREATED = 'toc_csr_created';

    public function isExternal(): bool
    {
        return \in_array($this, [
            self::TOC_CREATED_BY_PEOPLE_EXTERNAL,
            self::TOC_NEW_COMMENT_EXTERNAL,
            self::TOC_SOLVED_EXTERNAL,
            self::TOC_PENDING_TO_IN_PROGRESS_EXTERNAL,
        ], true);
    }
}
