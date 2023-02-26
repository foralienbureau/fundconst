<?php if( !defined('WPINC') ) die;

trait Fcon_Dform_Title {

    abstract public function get_form_title();

    protected function has_form_title() {

        $title = $this->get_form_title();

        return $title ? true : false;
    }

    protected function form_title() {

        if ( !$this->has_form_title() ) {
            return;
        }

        $title = $this->get_form_title();
    ?>
        <div class='fcon-form-title'><?php echo apply_filters( 'the_title', $title ); ?></div>
    <?php
    }


} // trait
