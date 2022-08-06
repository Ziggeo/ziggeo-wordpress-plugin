<?php
/**
 * @package Ziggeo
 */

class ParseSimpleTest extends WP_UnitTestCase {

	public function test_parsing_simple() {

		echo "\n";
		echo "\n" . 'Parsing Test';
		$this->assertEquals('<ziggeorecorder ziggeo-theme="modern" ziggeo-themecolor="red" ziggeo-width="360" ziggeo-height="240" ziggeo-limit="120" ziggeo-tags="wordpress,Guest,post,post_"></ziggeorecorder>', ziggeo_p_content_filter('[ziggeo]'));

		$this->assertEquals('<ziggeorecorder  ziggeo-responsive=false faceoutline ziggeo-title="Wordpress video" ziggeo-width="640" ziggeo-height="480" ziggeo-recordingwidth="640" ziggeo-recordingheight="480" ziggeo-popup="false" ziggeo-timelimit="0" ziggeo-countdown="3" ziggeo-recordings="0" ziggeo-theme="modern" ziggeo-themecolor="red" ziggeo-rerecordable="true" ziggeo-tags="wordpress,Guest,post,post_"></ziggeorecorder>', ziggeo_p_content_filter('[ziggeorecorder]'));

		$this->assertEquals('<ziggeoplayer  ziggeo-theme="modern" ziggeo-themecolor="red" ziggeo-width="360" ziggeo-height="240" ziggeo-video=""></ziggeoplayer>', ziggeo_p_content_filter('[ziggeoplayer]'));
	}
}
