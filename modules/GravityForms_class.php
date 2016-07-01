<?php
 
GFForms::include_addon_framework();

class ZiggeoIntegrationGravityFormsClass extends GFAddOn {

    protected $_version = 1.0;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'ziggeogravityforms';
	protected $_path = 'ziggeo/modules/GravityForms.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Ziggeo Video Field';
	protected $_short_title = 'Ziggeo Video Field';

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

    //Adding Ziggeo script if the preview is used
    public function scripts() {
        //Only if this is preview will we add a script to the head
        if($this->is_preview()) {
            $scripts = array(
                array(
                    'handle'  => 'ziggeo_sdk',
                    'src'     => '//assets-cdn.ziggeo.com/v1-stable/ziggeo.js',
                    'version' => $this->_version,
                    'enqueue' => array(
                        'field_types' => array('ZiggeoVideo')
                    )
                )
            );

            //return the combined scripts of the ones that did exist and the ones we added above
            return array_merge( parent::scripts(), $scripts );
        }

        //we send back the existing ones to make sure that GravityForms is not showing any errors due to no output
        return parent::scripts();;
    }

    //Adding Ziggeo CSS if the preview is used.
    public function styles() {
        //Lets check if we are in preview pages
        if($this->is_preview()) {
            //We are in the preview page
            $styles = array(
                array(
                    'handle'  => 'ziggeo_sdk_style',
                    'src'     => '//assets-cdn.ziggeo.com/v1-stable/ziggeo.css',
                    'version' => $this->_version,
                    'enqueue' => array(
                        'field_types' => array('ZiggeoVideo')
                    )
                )
            );

            return array_merge( parent::styles(), $styles );            
        }

        //we send back the existing ones to make sure that GravityForms is not showing any errors due to no output
        return parent::styles();
    }
}

if(class_exists('GF_Field')){
    class ZiggeoIntegrationGravityFormsClass_Field extends GF_Field {

        public $type = 'ZiggeoVideo';

        //Field title
        public function get_form_editor_field_title() {
            return 'Ziggeo Video Field';
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
        
        //Handles field drawing on the form itself (not in preview)
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
            $tabindex               = $this->get_tabindex();
            $logic_event            = !$is_form_editor && !$is_entry_detail ? $this->get_conditional_logic_event( 'keyup' ) : '';
            $placeholder_attribute  = $this->get_field_placeholder_attribute();
            $required_attribute     = $this->isRequired ? 'aria-required="true"' : '';
            $invalid_attribute      = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
            $disabled_text          = $is_form_editor ? 'disabled="disabled"' : '';
            $ziggeo_embedding_id    = 'embedding_' . $id;

            // Prepare the input tag for this field.
            $field = '<div class="ginput_container ginput_container_' . $this->type . '">';
            //we are adding id to div, so that it is available if needed for conditions and other things in GravityForms
            $field .= '<div id="' . $field_id . '" class="' . $class . '" ' . $tabindex . ' ' . $logic_event . ' ' . $placeholder_attribute . ' ' . $invalid_attribute . ' ' . $disabled_text . '>';

                //Loads the template based on the selection in our dropdown..
                $tmp = ziggeo_content_replace_templates(array($this->ziggeo_template_setting, $this->ziggeo_template_setting));

                if(strpos($tmp, 'ZiggeoWall') > -1) {
                    //we have video wall, most likely we will not do anything with it..
                }
                else {
                    //We now add the ID to the embedding so that we can have it fire up on the submitted event and fill out this field.. so if there are multiple ones on the form by any chance, we always update the one that the recording was made for..
                    $tmp = add_replace_template_parameter_value($tmp, 'ziggeo-id', $ziggeo_embedding_id, 'replace');
                }

                // We include the prepared $tmp into the field.
                $field .= $tmp;

                //the input field ID is changed so that there is just one ID field.
                $field .= '<input id="input_' . $id . '_field" name="input_' . $id . '" type="hidden" value="' . $videoToken . '" ' . $required_attribute . ' >';
                
                $field .= '<script type="text/javascript">' .
                                'ZiggeoApi.Events.on("submitted", function (data) {' .
                                    'if(data.id && ( data.id === "' . $ziggeo_embedding_id . '" || data.id.indexOf("' . $ziggeo_embedding_id . '") > -1 ) ){' .
                                        'document.getElementById("input_' . $id . '_field").value = data.video.token;' .
                                    '} ' .
                                '});' .
                            '</script>';
            $field .= '</div></div>';

            //@NOTE: The embedding will keep its value on refresh unless it is a cold refresh - while this is default browser behaviour, it is good to point it out in case someone has any issues with the same. I presume that at that time we could output a JS function as well that clears the fields out, otherwise since fields are hidden people will not be able to clear them out manually.

            return $field;
        }
    }
}