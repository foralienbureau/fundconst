<?php if( !defined('WPINC') ) die;

/** Class to display form template **/

class Fcon_Donation_Form {

    protected $landing;

    protected $format;

    protected $button_text;

    protected $form_title;


    public function __construct( $post, $format, $args = array() ) {

        $args = wp_parse_args( $args, $this->get_defaults() );

        if ( is_a( $post, 'Fcon_Landing' ) ) {
            $this->landing = $post;
        }
        else {
            $this->landing = new Fcon_Landing( $post );
        }

        if( in_array( $format, array( 'full', 'compact' ) ) ) {

            $this->format = $format;
        }
        else {
            $this->format = 'full';
        }

        $this->button_text = $args['button_text'];

        $this->form_title = $args['show_title'] ? $args['title_text'] : false;
    }

    protected function get_defaults() {

        return array(
            'button_text'   => __('Support', 'fcon'),
            'show_title'    => false,
            'title_text'    => __('Make donation', 'fcon')
        );
    }

    public function display() {

        if( !$this->can_display() ) {
            return;
        }

        $donationform = $this->get_template_data();

        if( $this->format == 'full' ) {

            $form = new Fcon_Dform_Full( $donationform );
            $form->print_markup();
        }
        else if( $this->format == 'compact' ) {

            $form = new Fcon_Dform_Compact( $donationform );
            $form->print_markup();
        }
        
    }

    protected function can_display() {

        if( !$this->landing->is_open() ) {
            return false;
        }

        if ( !$this->landing->has_connected_campaign() ) {
            return false;
        } 

        return true;
    }

    protected function get_template_data() {

        $data = array();

        $data['button_text']    = $this->button_text;
        $data['landing']        = $this->landing;
        $data['form_title']     = $this->form_title;

        // other settings ?

        return $data;
    }

} // class 
