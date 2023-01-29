<?php

// This file holds all / most of the functionality required to show the Templates Editor within the Ziggeo Video Plugin

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();




/////////////////////////////////////////////////
//                   BACKEND                   //
/////////////////////////////////////////////////

	// Settings and fields
	////////////////////////

	// register_setting( string $option_group, string $option_name, array $args = array() )
	register_setting('ziggeo_grp_translations',                             // option group
	                 'ziggeo_translations',                                 // option name
	                 array(
	                 	'default' => array(),                               // default values
	                 	//'sanitize_callback' => 'ziggeo_a_tp_validation'     // sanitize callback
	                 )
	);



/////////////////////////////////////////////////
//                  FRONTEND                   //
/////////////////////////////////////////////////

?>
<div>
	<h2>Translations Panel</h2>
	<form action="options.php" method="post">
		<?php
		wp_nonce_field('ziggeo_nonce_action', 'ziggeo_translations_nonce');
		settings_errors();
		settings_fields('ziggeo_templates');
		do_settings_sections('ziggeo_translations');

		?>
		<script>
			<?php
				$translations = get_option('ziggeo_translations');
				$prepared_translations = [];

				foreach($translations as $lang => $strings) {
					$remove = array(
						'ZiggeoApi.V2.Locale.mediaLocale.register({"',
						'ZiggeoApi.V2.Locale.mainLocale.register({"',
						'"},["language:' . $lang . '"],10);'
					);

					$strings = $strings['strings'];

					for($i = 0, $c = count($strings); $i < $c; $i++) {
						$strings[$i] = stripslashes($strings[$i]);
						$strings[$i] = str_replace($remove, '', $strings[$i]);
						$strings[$i] = explode('":"', $strings[$i]);
						$prepared_translations[$lang][$strings[$i][0]] = [$strings[$i][1]];
					}

				}

				$ret = json_encode($prepared_translations);
			?>
			function getExistingTranslations() {
				return <?php echo $ret; ?>;
			}
		</script>
		All of our supported languages can be seen here: <a href="https://ziggeo.com/features/language-support/">https://ziggeo.com/features/language-support/</a>.

		<div class="ziggeo_toolbar">
			<select id="ziggeo-languages">
				<option disabled=disabled>Please Wait</option>
			</select>
			<input id="ziggeo-strings-filter" value="">
		</div>
		<div id="ziggeo-translation-message">Please wait while we collect the available strings for translation</div>
		<div id="ziggeo-translation-fields" style="display: none;">
		</div>
		<div>
			<span id="ziggeo-translation-save" class="ziggeo-ctrl-btn">Save</span>
		</div>
	</form>
	<div id="ziggeo_messenger"><div id="ziggeo_message"></div><div>X</div></div>
</div>