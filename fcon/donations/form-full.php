<?php if( !defined('WPINC') ) die;

/** Full donation form template **/

class Fcon_Dform_Full extends Fcon_Dform_Base {

    protected function form_js() {
    ?>
    <script id="donationForm">
        function donationForm() {
            const formBase = Object.create(fconDformBase);

            formBase.onSubmit = function( event, $dispatch ) {

                formBase.regularSubmit($dispatch);
            }

            return formBase;
        }
    </script>
    <?php
    }

    public function print_markup() {

        $this->print_shared_store_js();

        $this->print_form_base_js();

        $this->form_js();

        $classes   = array($this->css_base);
        $classes[] = "{$this->css_base}--full";
        $classes[] = $this->has_period() ?  "has-period" : '';
        $classes[] = $this->has_form_title() ?  "has-title" : '';
    ?>
    <div
        x-data="donationForm"
        class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

        <?php if ( $this->has_form_title() ) { ?>
            <div class="<?php echo esc_attr( $this->css_base );?>__title">
                <?php $this->form_title(); ?>
            </div>
        <?php } ?>

        <form
            @submit.prevent="onSubmit($event, $dispatch)"
            method="post"
            class="<?php echo esc_attr( $this->css_base );?>__form">

            <?php if ( $this->has_period() ) { ?>
                <div class="<?php echo esc_attr( $this->css_base );?>__period">
                    <?php $this->period(); ?>
                </div>
            <?php } ?>

            <?php $this->form_error_message(); ?>

            <div class="<?php echo esc_attr( $this->css_base );?>__amount">
                <?php $this->amount(); ?>
            </div>

            <div class="<?php echo esc_attr( $this->css_base );?>__donor">
                <?php $this->name(); ?>
                <?php $this->email(); ?>
            </div>

            <div class="<?php echo esc_attr( $this->css_base );?>__agree">
                <?php $this->agree_checkbox();?>
            </div>

            <div class="<?php echo esc_attr( $this->css_base );?>__agree-news">
                <?php $this->agree_news_checkbox();?>
            </div>

            <div class="fcon-form__submit">
                <?php $this->form_submit();?>
            </div>
        </form>

    </div>
    <?php
    }
}
