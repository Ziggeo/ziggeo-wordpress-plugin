<?php
/**
 * @package Ziggeo
 */

class InitTest extends WP_UnitTestCase {

	public function test_info() {

		$this->assertNotEmpty(ZIGGEO_VERSION, 'Unable to read the plugin version');

		echo "\n";
		echo "\t\t\e[42;30m*********************************************\e[0m\n";
		echo "\t\t\e[42;30m* You are testing Ziggeo plugin version: " . ZIGGEO_VERSION . "\e[0m\n";
		echo "\t\t\e[42;30m*********************************************\e[0m\n";
		echo "\n";
		echo "\n";

		echo "  Any tests that are \e[42;30mmarked with green color\e[0m have passed the test\n";
		echo "  Any tests that are \e[43;30mmarked with orange color\e[0m have some warnings\n";
		echo "  Any tests that are \e[41;97mmarked with red color\e[0m have failed\n";
	}
}
