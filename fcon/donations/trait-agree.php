<?php if( !defined('WPINC') ) die;

trait Fcon_Dform_Agree {

    abstract public function get_form_id();

    abstract public function get_landing();


    protected function get_agree_terms_text() {

        $landing = $this->get_landing();

        $oferta_link = $landing->get_oferta_link();
        $privacy_link = $landing->get_privacy_link();

        return "Соглашаюсь с <a href='{$oferta_link}' class='fcon-agree-box__link'>офертой</a> и <a href='{$privacy_link}' class='fcon-agree-box__link'>политикой конфиденциальности</a>";
    }
    
    protected function agree_checkbox_js() {
    ?>
    <script id="agreeField">
        function agreeField() {
            return {
                field_error: false,

                init: function() {},

                onFieldChange: function( event ) {

                    if( !event.target.checked ) {
                        this.field_error = true;
                        this.$store.landingData.agree = 0;
                    }
                    else {
                        this.field_error = false;
                        this.$store.landingData.agree = 1;
                    }
                },

                onformInvalid: function( event ) {

                    if( event.detail.field == 'agree' ) {
                        this.field_error = 1;
                    }
                }
            }
        }
    </script>
    <?php
    }

    protected function agree_checkbox() {

        if ( !fcon()->is_registered_printed_js( 'agreeField' ) ) {

            $this->agree_checkbox_js();

            fcon()->register_printed_js( 'agreeField' );
        }

        $form_id = $this->get_form_id();

        $id = sprintf( "%s-agree", $form_id );

        $agree = $this->get_agree_terms_text();
    ?>
        <div
            x-data="agreeField"
            @dform-invalid.window="onformInvalid"
            :class="{'has-error': field_error}"
            class="fcon-agree-box">
            <input 
                @change="onFieldChange"
                type="checkbox"
                name="agree_terms"
                class="fcon-agree-box__input"
                value="1"
                id="<?php echo esc_attr($id); ?>"
                tabindex="0"
            >
            <label for="<?php echo esc_attr($id); ?>" class="fcon-agree-box__label"><?php echo $agree; ?></label>
            <div
                x-show="field_error"
                x-cloak
                class="fcon-agree-box__error fcon-field-error">Ваше согласие с условиями необходимо</div>
        </div>
    <?php
    }

} // trait
