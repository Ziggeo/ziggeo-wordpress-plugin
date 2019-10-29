<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


// - TEMPLATES - tab fields functions
//----------------------------------

//Function that starts the tab frames, starting with templates
function ziggeo_a_s_t_text() {

	//We show description and the list in the select option, since both are used to help customers, not to capture and handle any values
	?>
	<div class="ziggeo-frame" style="display: none;" id="ziggeo-tab_templates">
		<p>
			<?php
				_e('Welcome to templates - an easy way for you to set up the Ziggeo codes; you can call them from any post or page with a simple shortcode with the template ID, while everything is saved and handled for you by Ziggeo plugin.', 'ziggeo');
			?>
		</p>
		<p>
			<?php
				_e('You can start from the default shortcode and work your way from it, or choose one that is pre-set with some specific options', 'ziggeo');
			?>
		</p>
		<br id="ziggeo_editing">
	<?php
}

	//Field for ID of the template that we are editing or creating
	function ziggeo_a_s_t_id_field() {
		//On load, we do not need to load any data, just have the box empty. When we get back the response, that is when we need to capture the data..
		?>
		<input id="ziggeo_templates_id" name="ziggeo_video[templates_id]" size="50" type="text"
			placeholder="<?php _e('Give the template any name you wish here', 'ziggeo'); ?>" value="" />
		<?php
	}

	//This function build the interface that will help us show and manage the templates.
	//It will show a list of templates and over each, at the top right corner there should be options to edit and remove the same.
	function ziggeo_a_s_t_manager_field() {
		?>
		<div>
			<ul class="ziggeo-manage_list">
				<?php
					$list = ziggeo_p_templates_index();
					if($list) {
						foreach($list as $template_id => $template_code)
						{
							?><li><?php echo $template_id; ?> <span class="delete">x</span><span class="edit" data-template="<?php echo $template_code; ?>"><?php _e('edit'); ?></span></li><?php
						}
					}
					else {
						?><li><?php _e('No templates yet, please create one', 'ziggeo'); ?></li><?php
					}
				?>
				<?php //Edit should do //document.location += "#ziggeo_editing" while edit should do confirm() ?>
			</ul>
			<?php //We use this to help us see what action we need to make.. if edit, or delete, we store the old ID into its value, while it is empty if we create new ?>
			<input type="hidden" id="ziggeo_templates_manager" name="ziggeo_video[templates_manager]" value="">
		</div>
		<?php
	}

	//This shows textarea for templates editing, but also shows the available parameters / attributes that people can use on their template as well as the select field to select our starting template
	function ziggeo_a_s_t_editor_field() {
		//When we load the page the editor textarea should be empty, it should only have values once it is being saved (but does not need to)..

		//Lets show some useful info that was before way above
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

		//Lets make it easy to change (add or remove) the list of options that are available
		$templates = array(
			array(
				'value' => '[ziggeoplayer',
				'string' => __('Ziggeo Player', 'ziggeo')
			),
			array(
				'value' => '[ziggeorecorder',
				'string' => __('Ziggeo Recorder', 'ziggeo')
			),
			array(
				'value' => '[ziggeorerecorder',
				'string' => __('Ziggeo ReRecorder', 'ziggeo')
			),
			array(
				'value' => '[ziggeouploader',
				'string' => __('Ziggeo Uploader', 'ziggeo')
			)
		);

		//use add_filter('ziggeo_setting_available_templates', 'your-function-name') to change the options on fly if wanted
		// it needs to return modified $templates array.
		$templates = apply_filters('ziggeo_setting_available_templates', $templates);

		// We are showing the list of available templates to start from
		?>
		<label for="ziggeo_shorttags_list">Select the template base</label>
		<select id="ziggeo_shorttags_list" onchange="ziggeoPUITemplatesChange(this);">
			<?php
				/*
					Support for this will be removed
					<option value="[ziggeo"><?php //_e('Default', 'ziggeo'); ?></option>
				*/
			?>
			<?php
				for($i = 0, $c = count($templates); $i < $c; $i++) {
					?>
						<option value="<?php echo $templates[$i]['value'] ?>"><?php echo $templates[$i]['string'] ?></option>
					<?php
				}
			?>
			<?php // <option value="[ziggeoform">Ziggeo Form</option> ?>
		</select>
		<span id="ziggeo_parameters_advanced"><span>Easy Setup</span><span>&nbsp;</span></span>
		
		<?php //button would make more sense, but it would submit the form on click (its default action) and that is not what we want.. ?>
		<span id="ziggeo_templates_turn_to_new" style="display:none;"  onclick="ziggeoPUITemplatesTurnIntoNew();"><?php _e('Turn into new', 'ziggeo'); ?></span>

		<br><br>

		<?php
			//This is right before the editor. It is useful to show any errors, or messages from within a plugin, if it changes templates.
			//It can also be used to inject some custom JS code into the page to make more advanced changes.
			//We also include the list of all templates that are registered/shown to admin, in case it is needed for some plugin
			do_action('ziggeo_settings_before_editor', $templates);
		?>

		<?php //The actual template body that we will save ?>
		<textarea id="ziggeo_templates_editor" name="ziggeo_video[templates_editor]" rows="11" cols="50">[ziggeoplayer </textarea>

		<?php //The list of parameters to use in templates ?>

		<div id="ziggeo-params-holder">
			<div id="ziggeo_parameters_simple_section">
				<b><?php _e('Leave fields empty or checkboxes unchecked to not use it', 'ziggeo'); ?></b>
				<div>
					<?php
						include_once(ZIGGEO_ROOT_PATH . '/templates/template_parameters.php');

						ziggeo_template_write_simple_parameters_list();
					?>
				</div>
			</div>
			<div id="ziggeo_parameters_advanced_section">
				<b><?php _e('Ziggeo parameters that you can use in templates (Click to add)', 'ziggeo'); ?></b>
				<?php ziggeo_template_write_advanced_parameters_list(); ?>
			</div>
		</div>
		<br style="clear: both;">
		<div id="ziggeo_templates_update" class="button"><?php _e('Save Template', 'ziggeo'); ?></div>
		<?php
	}
?>