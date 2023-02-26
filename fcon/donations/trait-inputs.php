<?php if( !defined('WPINC') ) die;

trait Fcon_Dform_Inputs {

    protected function donor_js() {
    ?>
    <script id="donorField">
        function donorField() {
            return {
                field_name: '',
                field_value: '',
                validation: '',

                field_error: false,
                field_focus: false,
                field_valid: false,

                init: function() {

                    this.field_name = this.$root.getAttribute('field_name')
                    this.validation = this.$root.getAttribute('validation');
                },

                onFieldFocus: function( event ) {

                    this.field_focus = true;
                    this.field_error = false;
                },

                onFieldBlur: function( event ) {

                    this.field_focus = false;

                    let valid = this.fieldValidate();

                    if( valid ) {
                        this.field_valid = true;
                        this.field_error = false;

                        this.$store.landingData[this.field_name] = this.field_value;
                    }
                    else {
                        this.field_valid = false;
                        this.field_error = true;

                        // TODO - test this
                        this.$store.landingData[this.field_name] = '';
                    }
                },

                fieldValidate: function() {

                    let valid = false;

                    if( this.field_value.length > 0 ) {

                        valid = this.isFieldValid( this.field_value, this.validation );
                    }
                    
                    return valid;
                },

                isFieldValid( value, validation ) {

                    if( validation == 'text' ) {
                        return isTextValid( value );
                    }
                    else if( validation == 'email'  ) {
                        return isEmailValid( value );
                    }
                    else if( validation == 'telephone' ) {
                        return isTelephoneValid( value );
                    }
                },

                onformInvalid: function( event ) {

                    if( event.detail.field == this.field_name ) {
                        this.field_error = true;
                        this.field_invalid = true;
                    }
                }
            }
        }
    </script>
    <?php
    }

    protected function name() {

        if ( !fcon()->is_registered_printed_js( 'donorField' ) ) {

            $this->donor_js();

            !fcon()->register_printed_js( 'donorField' );
        }

        $label = 'Ваше имя';
        $hint  = 'Иван Иванов';

    ?>
    <div
        x-data="donorField"
        validation="text"
        field_name="name"
        @dform-invalid.window="onformInvalid"
        :class="{'has-error': field_error, 'focus': field_focus, 'valid': field_valid}"
        class="fcon-donor-field fcon-donor-field--name">
        <label for="donor_name" class="fcon-donor-field__label"><?php echo esc_html( $label );?></label>
        <input 
            x-model="field_value"
            @focus="onFieldFocus"
            @blur="onFieldBlur"
            type="text"
            name="donor_name"
            placeholder="<?php echo esc_attr( $hint );?>"
            value=""
            class="fcon-donor-field__input"
        />
        <div
            x-show="field_error"
            x-cloak
            class="fcon-donor-field__error fcon-field-error">Укажите ваше имя
        </div>
    </div>
    <?php
    }

    protected function email() {

        if ( !fcon()->is_registered_printed_js( 'donorField' ) ) {

            $this->donor_js();

            fcon()->register_printed_js( 'donorField' );
        }

        $label = 'Email';
        $hint = "ivan@address.com";
    ?>
    <div
        x-data="donorField"
        validation="email"
        field_name="email"
        @dform-invalid.window="onformInvalid"
        :class="{'has-error': field_error, 'focus': field_focus, 'valid': field_valid}"
        class="fcon-donor-field fcon-donor-field--email">
        <label for="donor_email" class="fcon-donor-field__label"><?php echo esc_html($label);?></label>
        <input 
            x-model="field_value"
            @focus="onFieldFocus"
            @blur="onFieldBlur"
            type="text"
            name="donor_email"
            placeholder="<?php echo esc_attr( $hint );?>"
            value=""
            class="fcon-donor-field__input"
        />
        <div
            x-show="field_error"
            x-cloak
            class="fcon-donor-field__error fcon-field-error">
            Укажите email в формате <?php echo esc_attr( $hint );?>
        </div>
    </div>
    <?php
    }
    
} // trait
