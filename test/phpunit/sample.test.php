<?php

namespace xipasduarte\WP\Plugin\PostExporter\Tests;

use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase {

	/**
	 * Setup mocking functions.
	 */
	public function setUp(): void {
		\WP_Mock::setUp();
	}

	/**
	 * Destroy mocking functions.
	 */
	public function tearDown() : void {
		\WP_Mock::tearDown();
	}

	/**
	 * @covers SampleTest
	 */
	public function test_sample() {
		$this->assertTrue(true);
	}
}
