<?php if( !defined('WPINC') ) die;

class Fcon_Blockbase  {

    protected $block;
    protected $prefix;
    protected $stack_post_id;

    public function __construct( $prefix, $block, $post_id ) {

        // Genereric object
        $this->prefix = $prefix;
        $this->block = $block;
        $this->stack_post_id = (int)$post_id;

        // Just to be sure we use the proper block in get_field
        acf_setup_meta( $this->block['data'], $this->block['id'], true );
    }

    public function reset() {

        // Reset postdata.
        // actually ACF do it after each block render
        // so we probably should not
        acf_reset_meta( $this->block['id'] );
    }

    public function build_wrapper_css() {

        $css = array();

        //var_dump($this->block);

        if( isset($this->block['align']) && !empty($this->block['align']) ) {
            $css[] = 'align' . $this->block['align'];
        }

        if( isset($this->block['className']) && !empty($this->block['className']) ) {
            $css[] = $this->block['className'];
        }

        $css[] = "fcon-block-".$this->prefix;

        return $css;
    }

    public function get_css_classes_string( $specific_classes = array() ) {

        $css = $this->build_wrapper_css( $specific_classes );

        $css = array_merge($css, $specific_classes);

        return implode(' ', $css);
    }

    public function get_id_string() {

        $anchor = '';

        if ( isset($this->block['anchor']) && !empty($this->block['anchor']) ) {
            $anchor = trim( $this->block['anchor'] );
        }

        $id = $anchor ? $anchor : '';
        
        return $id;
    }

} // class
