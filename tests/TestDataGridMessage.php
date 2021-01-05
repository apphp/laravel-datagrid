<?php

namespace Apphp\DataGrid\Tests;

use Tests\TestCase;
use Apphp\DataGrid\Message;


class TestDataGridMessage extends TestCase
{

    /**
     * Test empty message
     */
    public function testEmptyMessage(): void
    {
        $message = trim(Message::warning());
        $this->assertEmpty($message);
    }

    /**
     * Test message
     */
    public function testMessage(): void
    {
        $message = Message::warning('test');
        $this->assertStringContainsString('test', $message);
        $this->assertStringContainsString('alert-warning', $message);
    }

    /**
     * Test important message
     */
    public function testImportantMessage(): void
    {
        $message = Message::warning('test');
        $this->assertStringContainsString('alert-important', $message);
    }

    /**
     * Test not important message
     */
    public function testNotImportantMessage(): void
    {
        $message = Message::warning('test', false);
        $this->assertStringNotContainsString('alert-important', $message);
    }

}
