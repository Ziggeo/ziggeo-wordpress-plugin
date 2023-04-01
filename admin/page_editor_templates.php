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
	register_setting('ziggeo_grp_templates',                                // option group
	                 'ziggeo_templates',                                    // option name
	                 array(
	                 	'default' => array(),                               // default values
	                 	'sanitize_callback' => 'ziggeo_a_et_validation'     // sanitize callback
	                 )
	);

		// add_settings_section( string $id, string $title, callable $callback, string $page )
		add_settings_section('ziggeo_sct_templates_editor',                 // id
		                     '',                                            // title
		                     'ziggeo_a_et_editor_text',                     // callback
		                     'ziggeo_editor_templates'                      // page
		);

		// Templates list (shows templates, allows edit and delete)
		// add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
		add_settings_field('ziggeo_templates_manager',                      // id
							__('Manage your templates', 'ziggeo'),          // title
							'ziggeo_a_et_manager_field',                    // callback
							'ziggeo_editor_templates',                      // page
							'ziggeo_sct_templates_editor');                 // section

		// Template ID field
		add_settings_field('ziggeo_templates_id',                           // id
							__('Template ID', 'ziggeo'),                    // title
							'ziggeo_a_et_id_field',                         // callback
							'ziggeo_editor_templates',                      // page
							'ziggeo_sct_templates_editor');                 // section

		// Templates editor segment (lists available parameters and shows the editor textarea and dropbox for type of template to be created )
		add_settings_field('ziggeo_templates_editor',                       // id
							__('Template Editor', 'ziggeo'),                // title
							'ziggeo_a_et_editor_field',                     // callback
							'ziggeo_editor_templates',                      // page
							'ziggeo_sct_templates_editor');                 // section



	// Callbacks
	//////////////

		//Function that starts the tab frames, starting with templates
		function ziggeo_a_et_editor_text() {

			//We show description and the list in the select option, since both are used to help customers, not to capture and handle any values
			?>
			<div id="ziggeo-tab_templates">
				<p>
					<?php
						_e('Welcome to templates editor - a way for you to simply and quickly create codes you need or want on some of your page(s).', 'ziggeo');
					?>
				</p>
				<p>
					<?php

						_e('Use the editor bellow to manage your existing templates as well as to create new', 'ziggeo');
					?>
				</p>
				<p>
					<?php
						_e('You can start from the default shortcode and work your way from it, or choose one that is pre-set with some specific options', 'ziggeo');
					?>
				</p>
				<p>
					<?php
						_e('Once done, just add <strong>[ziggeotemplate ID]</strong> into the page, as you do, replace the ID with the actual ID of your template', 'ziggeo');
					?>
				</p>
				<br id="ziggeo_editing">
			<?php
		}

		//Field for ID of the template that we are editing or creating
		function ziggeo_a_et_id_field() {
			//On load, we do not need to load any data, just have the box empty. When we get back the response, that is when we need to capture the data..
			?>
			<input id="ziggeo_templates_id" name="ziggeo_video[templates_id]" size="50" type="text"
				placeholder="<?php _e('Give the template any name you wish here', 'ziggeo'); ?>" value="" />
			<p class="description">Please do not add space, or any special characters into the name</p>
			<?php
		}

		//This function build the interface that will help us show and manage the templates.
		//It will show a list of templates and over each, at the top right corner there should be options to edit and remove the same.
		function ziggeo_a_et_manager_field() {
			?>
			<div>
				<div class="ziggeo_templates">
					<?php
						$list = ziggeo_p_templates_index();
						if($list) {
							foreach($list as $template_id => $template_code)
							{
								if(is_array($template_code)) {
									// The new template codes
									$is_old_format = false;
								}
								else {
									// the old template codes
									$is_old_format = true;
								}

								if($template_id !== '') {

									?><div class="template">
										<div class="template_id"><?php echo $template_id; ?></div>
										<div class="template_code"
										     template-json="<?php echo ($is_old_format === true) ? ziggeo_p_template_code_to_object($template_code, true) : str_replace('"', "'", $template_code['json']) ; ?>"><?php echo ($is_old_format === true) ?  stripslashes($template_code) : stripslashes($template_code['shortcode']); ?></div>
										<div class="actions">
											<div class="use"><?php _e('Use', 'ziggeo'); ?></div>
											<div class="edit"><?php _e('Edit', 'ziggeo'); ?></div>
											<div class="delete"><?php _e('Remove', 'ziggeo'); ?></div>
										</div>
									</div><?php
								}
							}
						}
						else {
							?><div class="no-templates"><?php _e('No templates yet, please create one', 'ziggeo'); ?></div><?php
						}
					?>
					<?php //Edit should do //document.location += "#ziggeo_editing" while x should do confirm() ?>
				</div>
				<?php //We use this to help us see what action we need to make.. if edit, or delete, we store the old ID into its value, while it is empty if we create new ?>
				<input type="hidden" id="ziggeo_templates_manager" name="ziggeo_video[templates_manager]" value="">
			</div>
			<?php
		}

		function ziggeo_a_et_editor_field() {
			include_once(ZIGGEO_ROOT_PATH . '/templates/template_parameters.php');

			?>
			<div id="ziggeo_templates_types" style="display: none;">
				<?php
					_e('The different parameters have the following value types:');
				?>
				<ol>
					<li><?php _e('Integer - after equal you simply add the number, no quotes', 'ziggeo'); ?></li>
					<li><?php _e('Float - this is integer with decimal precision', 'ziggeo'); ?></li>
					<li><?php _e('Boolean - you can just remove parameter (which equals to false) or add it and will be seen as true', 'ziggeo'); ?></li>
					<li><?php _e('String - value holding numbers, characters and spaces (as needed), which must be enclosed in quotation marks on both sides (on start and end)', 'ziggeo'); ?></li>
					<li><?php _e('Array - similar to string as it needs to be enclosed with quotation marks, but you can select multiple options, separating them with comma', 'ziggeo');?></li>
					<li><?php _e('JSON - data formated as per JSON specification', 'ziggeo'); ?></li>
				</ol>
			</div>
			<?php

			//button would make more sense, but it would submit the form on click (its default action) and that is not what we want.. ?>
			<span id="ziggeo_templates_turn_to_new" style="display:none;"  onclick="ziggeoPUITemplatesTurnIntoNew();"><?php _e('You are currently editing template. Click to save as new template', 'ziggeo'); ?></span>

			<br><br>
			<?php
				// The actual template body that we will save
				// We do however keep this one only as a read only from now on (v3.0)
			?>
			<textarea id="ziggeo_templates_editor" name="ziggeo_video[templates_editor]" rows="11" cols="50" readonly="true">[ziggeoplayer </textarea>

			<?php
			//use add_filter('ziggeo_setting_available_templates', 'your-function-name') to change the options on fly if wanted
			// it needs to return modified $templates array.
			$templates = apply_filters('ziggeo_setting_available_templates', array());

				//This is right before the editor. It is useful to show any errors, or messages from within a plugin, if it changes templates.
				//It can also be used to inject some custom JS code into the page to make more advanced changes.
				//We also include the list of all templates that are registered/shown to admin, in case it is needed for some plugin
				do_action('ziggeo_settings_before_editor', $templates);

			// We are showing the list of available templates to start from
			?>
			<div class="template_creator">
				<div class="row">
					<div class="column">
						<p for="ziggeo_shorttags_list">Select the template base</p>
						<select id="ziggeo_shorttags_list" onchange="ziggeoPUITemplatesChange(this);">
							<?php
								for($i = 0, $c = count($templates); $i < $c; $i++) {
									?>
										<option value="<?php echo $templates[$i]['value'] ?>"><?php echo $templates[$i]['string'] ?></option>
									<?php
								}
							?>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="column">
						<p>Add Parameter Name</p>
						<input class="ziggeo-autocomplete-input"
						       field-hook="template_editor_autocomplete_param_name"
						       field-data="<?php echo esc_attr(json_encode(ziggeo_get_template_parameters_list())); ?>"
						       id="parameter-name">
					</div>
					<div class="column">
						<p>Select Parameter Type</p>
						<select id="parameter-type">
							<option value="integer" title="A whole number">Integer</option>
							<option value="float" title="Number with decimal point">Float</option>
							<option value="bool" title="true or false values">Boolean</option>
							<option value="string" title="Any text value">String</option>
							<option value="array" title="Array of values,comma separated values">Array</option>
							<option value="json" title="uses JSON format">JSON</option>
						</select>
					</div>
					<div class="column">
						<p>Set Parameter Value</p>
						<input id="parameter-value">
					</div>
					<div class="column">
						<p>Actions</p>
						<span class="ziggeo-ctrl-btn ziggeo-of-simple-form" tabindex="0"
						      data-fields="name:parameter-name,value:parameter-value,type:parameter-type"
						      data-function="ziggeoTemplatesParametersAdd"
						      data-post-action="clear">+</span>
						<span class="ziggeo-ctrl-btn ziggeo-of-simple-form btn-warning" tabindex="0"
						      data-fields="name:parameter-name,value:parameter-value,type:parameter-type"
						      data-function="ziggeoTemplatesParametersRemove"
						      data-post-action="clear">-</span>
					</div>
				</div>
				<div class="row">
					<div class="column">
						<p>Some of the parameters can be found in bellow list. For complete list of available parameters please our <a href="https://ziggeo.com/docs/sdks/javascript/browser-integration/parameters?utm_source=wordpress">parameters page</a>.</p>
						<p>Please add <i>%ZIGGEO_MEDIA_TOKEN%</i> into parameter when you want to pass the media (video) token</p>
					</div>
					</div>
				</div>
				<div class="row">
					<div class="column extending-list">
						<?php ziggeo_template_write_advanced_parameters_list(true, 'quarter'); ?>
					</div>
				</div>
			</div>


			<br style="clear: both;">
			<div id="ziggeo_templates_update" class="ziggeo-ctrl-btn" style="display: none;"><?php _e('Update Template', 'ziggeo'); ?></div>
			<div id="ziggeo_templates_save" class="ziggeo-ctrl-btn"><?php _e('Save Template', 'ziggeo'); ?></div>
			<?php
		}




/////////////////////////////////////////////////
//                  FRONTEND                   //
/////////////////////////////////////////////////

?>
<div>
	<h2>Templates Editor</h2>

	<form action="options.php" method="post">
		<?php
		wp_nonce_field('ziggeo_nonce_action', 'ziggeo_editor_templates_nonce');
		settings_errors();
		settings_fields('ziggeo_templates');
		do_settings_sections('ziggeo_editor_templates');
		?>
	</form>
	<div id="ziggeo_messenger"><div id="ziggeo_message"></div><div>X</div></div>
</div>