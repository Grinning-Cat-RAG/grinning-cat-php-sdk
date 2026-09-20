<?php

namespace DataMat\GrinningCat\Tests;

use DataMat\GrinningCat\Tests\Traits\TestTrait;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class CustomEndpointTest extends TestCase
{
    use TestTrait;

    /**
     * @throws \JsonException|Exception
     */
    public function testGetGlobalMessageSuccess(): void
    {
        $expected = [
            'management_message' => 'Management message',
            'management_active' => true,
            'global_message' => 'Global message',
            'show_global_msg' => true,
        ];

        $client = $this->getGrinningCatClient(null, $expected);

        self::assertSame($expected, $client->custom()->getGlobalMessage());
    }
}
