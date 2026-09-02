<?php

declare(strict_types=1);

namespace App\Tests\Snowflake\Fixtures;

use App\Snowflake\Attribute\Column;

/**
 * Minimal Snowflake-backed resource used across src/Snowflake unit tests,
 * mirroring the shape of App\Dto\Snowflake\VendorItemCost without depending
 * on it directly.
 */
class DummyResource
{
    public function __construct(
        #[Column('c.SITE')]
        public readonly string $site,
        #[Column('c.ITEM')]
        public readonly string $item,
        #[Column('i.DSCA')]
        public readonly ?string $description = null,
        // Intentionally has no #[Column] attribute, to exercise the "unmapped property" path.
        public readonly ?string $untracked = null,
    ) {
    }
}
