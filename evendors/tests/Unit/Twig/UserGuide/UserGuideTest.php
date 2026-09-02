<?php

declare(strict_types=1);

namespace App\Tests\Unit\Twig\UserGuide;

use App\Twig\UserGuide\UserGuide;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class UserGuideTest extends TestCase
{
    public function testGetMapping(): void
    {
        $this->assertSame([1781 => [
            'name' => 'header.menu.user_guide.evendor_help',
            'filename' => 'DMS#1886.pdf',
        ]], UserGuide::getMapping('/'));

        $this->assertSame([6341 => [
            'name' => 'header.menu.user_guide.vendor_waranty_claim_help',
            'filename' => 'DMS#6508.pdf',
        ]], UserGuide::getMapping('/vendor-warranty-claim/'));

        $this->assertSame([
            1287 => [
                'name' => 'header.menu.user_guide.supplier_corrective_action_request_help',
                'filename' => 'DMS#1374_SCAR_Evendor_user_guide.pdf',
            ],
            555 => [
                'name' => 'header.menu.user_guide.8d_format',
                'filename' => 'DMS#604_Corrective_Preventive_Action_Request.xlsx',
            ],
        ], UserGuide::getMapping('/supplier-corrective-action-request/'));

        $this->assertSame([], UserGuide::getMapping('/invalid-path'));
    }
}
