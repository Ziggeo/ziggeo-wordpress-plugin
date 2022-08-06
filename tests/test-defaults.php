<?php
/**
 * @package Ziggeo
 */

class DefaultsTest extends WP_UnitTestCase {

	public function test_constants() {

		// Test the plugin constants
		echo "\n";
		echo "\n" . 'Testing Constants';
		$this->assertEquals('1', ZIGGEO_YES);
		$this->assertEquals('0', ZIGGEO_NO);

		// Test Defaults
		echo "\n" . 'Testing Defaults';
		$this->assertEquals('ziggeo-theme="modern" ziggeo-themecolor="red" ziggeo-width="360" ziggeo-height="240"', ZIGGEO_DEFAULTS_PLAYER);
		$this->assertEquals('ziggeo-theme="modern" ziggeo-themecolor="red" ziggeo-width="360" ziggeo-height="240" ziggeo-limit="120"', ZIGGEO_DEFAULTS_RECORDER);
		$this->assertEquals('ziggeo-theme="modern" ziggeo-themecolor="red" ziggeo-width="360" ziggeo-height="240" ziggeo-limit="120" ziggeo-allowrecord="false"', ZIGGEO_DEFAULTS_UPLOADER);
		$this->assertEquals('ziggeo-theme="modern" ziggeo-themecolor="red" ziggeo-width="360" ziggeo-height="240" ziggeo-limit="120" ziggeo-rerecordable="true"', ZIGGEO_DEFAULTS_RERECORDER);
		$this->assertEquals('ziggeo-theme="modern" ziggeo-themecolor="red" ziggeo-width="360" ziggeo-height="240" ziggeo-limit="120" ziggeo-allowscreen="true"', ZIGGEO_DEFAULTS_SCREEN);

	}
}
