<?php
 
GFForms::include_addon_framework();

class ZiggeoIntegrationGravityFormsClass extends GFAddOn {

    protected $_version = 1.0;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'ziggeogravityforms';
	protected $_path = 'ziggeo/modules/GravityForms.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Ziggeo Video Aid';
	protected $_short_title = 'Ziggeo Video Aid';

    private static $_instance = null;

    public static function get_instance() {
		if(self::$_instance == null) {
            self::$_instance = new self();
		}

		return self::$_instance;
	}

    public function pre_init() {
		parent::pre_init();

		if($this->is_gravityforms_supported() && class_exists('GF_Field')) {
			GF_Fields::register( new ZiggeoIntegrationGravityFormsClass_Field() );
		}
	}
    
    public function init_admin() {
		parent::init_admin();

		add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
		add_action( 'gform_field_appearance_settings', array( $this, 'field_appearance_settings' ), 10, 2 );
	}

    //Add the custom setting for the Simple field to the Appearance tab.
	public function field_appearance_settings( $position, $form_id ) {
		// Add our custom setting just before the 'Custom CSS Class' setting.

		if( $position == 250 ) {
			?>
			<li class="ziggeo_template_setting field_setting">
				<label for="ziggeo_template_setting">Choose your template:
					<?php gform_tooltip( 'ziggeo_template_setting' ) ?>
				</label>
                <select id="ziggeo_template_setting" class="fieldwidth-1" onchange="ziggeo_integration_gravityforms_admin_select(this)">
                    <?php //fill out the templates ?>
                    <option disabled=disabled>Select a template</option>

                    <?php
                    //index function changes " to ' to make sure that we do not have issues with TinyMCE, so we can use it here as well.
                    $list = ziggeo_templates_index();
                    if($list) {
                        foreach($list as $template => $value)
                        {
                            ?><option value="<?php echo $template; ?>"><?php echo $template; ?></option><?php
                        }
                    }
                    ?>
                </select>
			</li>

			<?php
		}
	}

    public function tooltips( $tooltips ) {
        $tooltips['ziggeo_template_setting'] = '<h6>Ziggeo Templates</h6>Select the template in the dropdown best matching your requirements';
        return $tooltips;
    }
}

if(class_exists('GF_Field')){
    class ZiggeoIntegrationGravityFormsClass_Field extends GF_Field {

        public $type = 'ZiggeoVideo';

        //Field title
        public function get_form_editor_field_title() {
            return 'Ziggeo Video Aid';
        }

        //Making the field shown under Advanced fields option
        public function get_form_editor_button() {
            return array(
                'group' => 'advanced_fields',
                'text'  => $this->get_form_editor_field_title(),
            );
        }

        //Settings that we want to allow to be changed about our field on form
        function get_form_editor_field_settings() {
            return array(
                'label_setting',
                'description_setting',
                'rules_setting',
                'size_setting',
                'admin_label_setting',
                'default_value_setting', //maybe if they add video token, we play it on the form, rather than showing a recorder?
                'visibility_setting',
                'conditional_logic_field_setting',
                'ziggeo_template_setting'
            );
        }

        //We are supporting conditions so we return true here
        public function is_conditional_logic_supported() {
            return true;
        }

        //Adds the code to save the selections in the settings of the field
        public function get_form_editor_inline_script_on_page_render() {

            // set the default field label for the simple type field
            $script = 'function SetDefaultValues_ZiggeoVideo(field) { field.label = "' . $this->get_form_editor_field_title() . '";}';

            // initialize the fields custom settings
            $script .= "
            jQuery(document).bind(
                'gform_load_field_settings', function (event, field, form) {" .
                    "var ziggeo_template_setting = field.ziggeo_template_setting === undefined ? 'Select a template' : field.ziggeo_template_setting;" .
                    "jQuery('#ziggeo_template_setting').val(ziggeo_template_setting);" .
                "});";

            // saving the simple setting
            $script .= 'function Setziggeo_template_settingSetting(value) { SetFieldProperty(\'ziggeo_template_setting\', value); }';

            return $script;
        }
        
        //Handles field drawing on the form itself (in admin)
        public function get_field_input( $form, $value = '', $entry = null ) {

            $id              = absint( $this->id );
            $form_id         = absint( $form['id'] );
            $is_entry_detail = $this->is_entry_detail();
            $is_form_editor  = $this->is_form_editor();

            // Prepare the value of the input ID attribute.
            $field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

            $videoToken = esc_attr( $value );

            // Get the value of the inputClass property for the current field.
            $inputClass = $this->inputClass;

            // Prepare the input classes.
            $size         = $this->size;
            $class_suffix = $is_entry_detail ? '_admin' : '';
            $class        = $size . $class_suffix . ' ' . $inputClass;

            // Prepare the other input attributes.
            $tabindex              = $this->get_tabindex();
            $logic_event           = ! $is_form_editor && ! $is_entry_detail ? $this->get_conditional_logic_event( 'keyup' ) : '';
            $placeholder_attribute = $this->get_field_placeholder_attribute();
            $required_attribute    = $this->isRequired ? 'aria-required="true"' : '';
            $invalid_attribute     = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
            $disabled_text         = $is_form_editor ? 'disabled="disabled"' : '';

            // Prepare the input tag for this field.
            $field = '<div class="ginput_container ginput_container_' . $this->type . '">';
            $field .= '<div id="' . $field_id . '" class="' . $class . '" ' . $tabindex . ' ' . $logic_event . ' ' . $placeholder_attribute . ' ' . $required_attribute . ' ' . $invalid_attribute . ' ' . $disabled_text . '>';

                //Loads the template based on the selection in our dropdown..
                $field .= ziggeo_content_replace_templates(array($this->ziggeo_template_setting, $this->ziggeo_template_setting));

                $field .= '<input id="input_' . $id . '" name="input_' . $id . '" type="hidden" value="' . $videoToken . '">';
                
            $field .= '</div></div>';

            return $field;
        }
    }
}

//Seems that we need this code as well otherwise it will not show up the field code on public or preview ;/
add_filter( 'gform_field_input', 'ziggeoIntegrationGravityForms_public', 10, 5 );
function ziggeoIntegrationGravityForms_public( $input, $field, $value, $lead_id, $form_id ) {

    if($field->type === 'ZiggeoVideo' && !is_admin())
    {
        $id = absint( $field->id );
        $is_entry_detail = false;
        $is_form_editor  = false;

        // Prepare the value of the input ID attribute.
        $field_id = $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

        $videoToken = esc_attr( $value );

        // Get the value of the inputClass property for the current field.
        $inputClass = $field->inputClass;

        // Prepare the input classes.
        $size         = $field->size;
        $class_suffix = $is_entry_detail ? '_admin' : '';
        $class        = $size . $class_suffix . ' ' . $inputClass;

        // Prepare the other input attributes.
        $logic_event           = $field->conditionalLogic;
        $placeholder_attribute = $field->placeholder;
        $required_attribute    = $field->isRequired ? 'aria-required="true"' : '';
        $invalid_attribute     = $field->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
        $tabindex              = $field->get_tabindex();
        $disabled_text         = $is_form_editor ? 'disabled="disabled"' : '';

        // Prepare the input tag for this field.
        $input = '<div class="ginput_container ginput_container_' . $field->type . '">';
        $input .= '<div id="' . $field_id . '" class="' . $class . '" ' . $tabindex . ' ' . $logic_event . ' ' . $placeholder_attribute . ' ' . $required_attribute . ' ' . $invalid_attribute . ' ' . $disabled_text . '>';

            //Loads the template based on the selection in our dropdown..
            $input .= ziggeo_content_replace_templates(array($field->ziggeo_template_setting, $field->ziggeo_template_setting));

            $input .= '<input id="input_' . $id . '" name="input_' . $id . '" type="hidden" value="' . $videoToken . '">';
            
        $input .= '</div></div>';
/*
        // Prepare the input tag for this field.
        $input = '<div id="' . $field_id . '" class="' . $class . '" ' . $logic_event . ' ' . $placeholder_attribute . ' ' . $required_attribute . ' ' . $invalid_attribute . '>';
            $input = '<ziggeo ';
                if($videoToken !== '') {
                    $input .= ' ziggeo-video="' . $videoToken . '"';                
                }
            $input .= '></ziggeo>';
            $input .=   '<script type="text/javascript">
                            ZiggeoApi.Events.on("submitted", function (data) {
                                document.getElementById("input_' . $id . '").value = data.video.token;
                            });
                        </script>';
            $input .= '<input id="input_' . $id . '" name="input_' . $id . '" type="hidden" value="' . $videoToken . '">';
*/
    }

    return $input;
}

////////// ******** TEST TO SAVE THE SELECTED SETTINS - nothing gets called so far.

/*
add_filter( 'gform_save_field_value', 'save_field_value', 10, 4 );
function save_field_value($value, $lead, $field, $form) {
echo "\n<br>";
echo 'save_field_value';
echo "\n<br>";
    var_dump($value2);
    var_dump($lead);
    var_dump($field);
    var_dump($form);
    die();
}

add_filter( 'gform_pre_form_settings_save', 'preform_settings_save', 10, 2 );
function preform_settings_save($one, $two) {
echo "\n<br>";
echo 'preform_settings_save';
echo "\n<br>";
    var_dump($one, $two2);
    exit;
}

add_filter( 'gform_form_settings', 'my_custom_form_setting', 10, 2 );
function my_custom_form_setting( $settings, $form ) {
echo "\n<br>";
echo 'my_custom_form_setting';
echo "\n<br>";
    var_dump($settings2);
    var_dump($form);
?><script>alert('my_custom_form_setting');</script><?php
    die();
}

add_filter( 'gform_get_input_value', 'decode_field', 10, 4 );
function decode_field( $value, $entry, $field, $input_id ) {
echo "\n<br>";
echo 'save_field_value';
echo "\n<br>";
    var_dump($value2);
    var_dump($entry);
    var_dump($field);
    var_dump($input_id);
    die();
}*/