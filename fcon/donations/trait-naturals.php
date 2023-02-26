<?php if( !defined('WPINC') ) die;

trait Fcon_Dform_Natural_Amount {

    abstract public function get_form_id();

    abstract public function get_currency_label();


    protected function natural_amount_selection_js() {
    ?>
    <script>
        function naturalAmountSelection() {
            return {
                flex_value: null,
                flex_focus: false,
                flex_valid: false,
                flex_error: false,

                natural_inactive: false,
                natural_selection: 0,

                min: 0,
                max: 0,

                init: function() {

                    this.min = this.$store.landingData.amount_limits['min'];
                    this.max = this.$store.landingData.amount_limits['max'];

                    this.natural_selection = this.$store.landingData.natural_selection;
                    
                    this.$watch( '$store.landingData.amount', value => this.syncAmount(value) );
                },

                syncAmount: function( amount ) {

                    if ( this.$store.landingData.amount_selection_type == 'flex' ) {

                        this.flex_value = amount > 0 ? amount : null;
                        this.natural_inactive = true;

                        let valid = this.flexValidate();

                        if( valid ) {
                            this.flex_valid = true;
                            this.flex_error = false;
                        }
                        else {
                            this.flex_valid = false;
                            this.flex_error = true;
                        }
                    }
                    else {

                        this.natural_selection = this.$store.landingData.natural_selection;
                        this.natural_inactive = false;

                        this.flexReset();
                    }
                },

                onLess: function( event ) {

                    this.natural_inactive = false;

                    if ( this.natural_selection > 1 ) {
                        this.natural_selection = this.natural_selection - 1;
                    }
                    else {
                        this.natural_selection = 0;
                    }

                    this.flexReset();

                    this.$store.landingData.amount_selection_type = 'fix';
                    this.$store.landingData.natural_selection = this.natural_selection;
                    this.$store.landingData.amount = this.natural_selection * this.$store.landingData.natural_price;
                },

                onMore: function( event ) {

                    this.natural_inactive = false;
                    this.natural_selection = this.natural_selection + 1;

                    this.flexReset();

                    this.$store.landingData.amount_selection_type = 'fix';
                    this.$store.landingData.natural_selection = this.natural_selection;
                    this.$store.landingData.amount = this.natural_selection * this.$store.landingData.natural_price;
                    
                },
                
                onflexFocus: function( event ) {

                    this.flex_focus = true;

                    this.natural_inactive = true;
                },

                onflexBlur: function( event ) {

                    this.flex_focus = false;

                    let valid = this.flexValidate();

                    if( valid ) {
                        this.flex_valid = true;
                        this.flex_error = false;

                        this.$store.landingData.amount = this.flex_value;
                        this.$store.landingData.amount_selection_type = 'flex';
                    }
                    else {
                        this.flex_valid = false;
                        this.flex_error = true;

                        // TODO - test this
                        this.$store.landingData.amount = 0;
                        this.$store.landingData.amount_selection_type = 'flex';
                    }
                },

                flexReset: function() {

                    this.flex_value = null;
                    this.flex_focus = false;
                    this.flex_valid = false;
                    this.flex_error = false;
                },

                flexValidate: function() {

                    let valid = false;

                    if( this.flex_value  > 0 ) {

                        valid = isAmountValid( this.flex_value, this.min, this.max);
                    }
                    
                    return valid;
                },

                onformInvalid: function( event ) {

                    if( event.detail.field == 'amount' ) {
                        this.flex_error = true;
                        this.flex_valid = false;
                    }
                }
            }
        }
    </script>
    <?php
    }

    protected function natural_amount_selection() {
    
        $currency = $this->get_currency_label();

        if ( !fcon()->is_registered_printed_js( 'naturalAmountSelection' ) ) {

            $this->natural_amount_selection_js();

            fcon()->register_printed_js( 'naturalAmountSelection' );
        }
    ?>
        <div
            x-data="naturalAmountSelection"
            @dform-invalid.window="onformInvalid"
            class="fcon-natural-amount">

            <div
                :class="{'inactive': natural_inactive}"
                class="fcon-natural-amount__control fcon-natural-control">

                <div class="fcon-natural-control__input">
                    <div class="fcon-natural-input">
                        <div 
                            @click.prevent="onLess"
                            class="fcon-natural-input__less">-</div>
                        <div class="fcon-natural-input__number" x-text="natural_selection"></div>
                        <div
                            @click.prevent="onMore"
                            class="fcon-natural-input__more">+</div>
                    </div>
                </div>

                <div class="fcon-natural-control__result">
                    <div class="fcon-natural-result">
                        <span class="fcon-natural-result__amount" x-text="natural_selection * $store.landingData.natural_price"></span>
                        <dfn><?php echo esc_html( $currency );?></dfn>
                    </div>
                </div>
            </div>

            <div class="fcon-natural-amount__or"><?php _e('or', 'fcon') ;?></div>

            <div class="fcon-natural-amount__flex">
                <div
                    :class="{'has-error': flex_error, 'focus': flex_focus, 'valid': flex_valid}"
                    class="fcon-amount__flex fcon-flex-amount">
                    <input 
                        x-model="flex_value"
                        x-ref="flex_amount"
                        inputmode="numeric"
                        @focus="onflexFocus"
                        @blur="onflexBlur"
                        @input="$event.target.value=$event.target.value.replace(/\D+/g,'')"
                        type="text"
                        name="flex_amount"
                        value=""
                        class="fcon-flex-amount__input"
                        placeholder="<?php _e( 'Your amount', 'fcon' );?>">
                </div>
            </div>

            <div
                x-show="flex_error"
                x-cloak
                class="fcon-natural-amount__error fcon-field-error">Укажите сумму между <span x-text="min"></span> и <span x-text="max"></span><dfn><?php echo esc_html( $currency );?></dfn>
            </div>
        </div><!-- amount -->
    <?php
    }

} // trait
