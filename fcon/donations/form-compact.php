<?php if( !defined('WPINC') ) die;

/** Compact donation form template **/

class Fcon_Dform_Compact extends Fcon_Dform_Base {


    public function print_markup() {

        $this->print_shared_store_js();

        $classes   = array($this->css_base);
        $classes[] = "{$this->css_base}--compact";
        $classes[] = $this->has_period() ?  "has-period" : '';
        $form_id   = $this->form_id;
    ?>
    <div
        x-data="{}"
        class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

        <div class="<?php echo esc_attr( $this->css_base );?>__form">

            <?php if ( $this->has_period() ) { ?>
                <div class="<?php echo esc_attr( $this->css_base );?>__period">
                    <?php $this->period(); ?>
                </div>
            <?php } ?>

            <?php if ( $this->has_form_title() ) { ?>
                <div class="<?php echo esc_attr( $this->css_base );?>__title">
                    <?php $this->form_title(); ?>
                </div>
            <?php } ?>

            <div class="<?php echo esc_attr( $this->css_base );?>__amount">
                <?php $this->amount(); ?>
            </div>

            <div class="fcon-form__submit">
                <button
                    @click.prevent="$dispatch('modal-open'); $dispatch('body-lock')"
                    type="submit"
                    class="fcon-donate-button">
                    <span><?php echo esc_html($this->button_text);?></span>
                </button>
            </div>

        </div>
    </div>
    <?php
    }

} // class

