<?php if( !defined('WPINC') ) die;

trait Fcon_Dform_Simple_Amount {

    abstract public function get_form_id();

    abstract public function get_currency_label();


    protected function get_fix_variants_count() {
        // this depends on design
        // for simplicity we don't load it from options
        return 4;
    }

    protected function simple_amount_selection_js() {
    ?>
    <script id="simpleAamountSelection">
        function simpleAamountSelection() {
            return {
                flex_value: null,
                flex_focus: false,
                flex_valid: false,
                flex_error: false,

                fix_variants: [],
                fix_selection: 0,

                min: 0,
                max: 0,

                init: function() {

                    this.min = this.$store.landingData.amount_limits['min'];
                    this.max = this.$store.landingData.amount_limits['max'];

                    this.setFixVariants();

                    this.$watch( '$store.landingData.amount', value => this.syncAmount(value) );

                    this.$watch( '$store.landingData.period', value => this.setFixVariants(value) );
                },

                syncAmount: function( amount ) {

                    if ( this.$store.landingData.amount_selection_type == 'flex' ) {

                        this.flex_value = amount > 0 ? amount : null;
                        
                        this.fixReset();

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

                        this.fix_selection = amount;

                        if( this.fix_variants.length === 0) {
                            this.setFixVariants();
                        }
                        
                        // selection
                        for (i = 0; i < this.fix_variants.length; i++) {

                            let key = "fix_amount_" + i;

                            if ( this.fix_variants[i] == this.fix_selection ) {

                                this.$refs[key].checked = true;
                            }
                        }

                        this.flexReset();
                    }
                },

                setFixVariants: function( period = null ) {

                    if( period === null ) {
                        period = this.$store.landingData.period;
                    }

                    this.fix_variants = this.$store.landingData.amount_variants[period];
                    this.fix_selection = this.$store.landingData.default_amount[period]

                    for (i = 0; i < this.fix_variants.length; i++) {

                        let key = "fix_amount_" + i;

                        this.$refs[key].value = this.fix_variants[i];

                        if( this.flex_valid === true ) {

                            continue; // successfully set flex value
                        }
                        else if ( this.fix_variants[i] == this.fix_selection ) {

                            this.$refs[key].checked = true;
                        }
                    } 
                },

                onFixChange: function( event ) {

                    let value = parseInt(event.target.value);

                    this.fix_selection = value;

                    this.flexReset();

                    this.$store.landingData.amount = value;
                    this.$store.landingData.amount_selection_type = 'fix';
                },

                fixReset: function() {

                    this.fix_selection = null;

                    for (i = 0; i < this.fix_variants.length; i++) {

                        let key = "fix_amount_" + i;

                        this.$refs[key].checked = false;
                    }
                },

                onflexFocus: function( event ) {

                    this.flex_focus = true;

                    this.fixReset();
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

    protected function simple_amount_selection() {

        if ( !fcon()->is_registered_printed_js( 'simpleAamountSelection' ) ) {

            $this->simple_amount_selection_js();

            fcon()->register_printed_js( 'simpleAamountSelection' );
        }

        $currency = $this->get_currency_label();

        $form_id = $this->get_form_id();

        $varians_num = $this->get_fix_variants_count();
    ?>
        <div
            x-data="simpleAamountSelection"
            @dform-invalid.window="onformInvalid"
            class="fcon-amount">

            <?php for( $i = 0; $i < $varians_num; $i++ ) { ?>

                <div class="fcon-amount__fix fcon-fix-amount">
                    <?php $id = sprintf( "%s-fix-amount-%s", $form_id, $i ); ?>
                    <input
                        x-ref="fix_amount_<?php echo $i;?>"
                        @change="onFixChange"
                        type="radio"
                        name="fix_amount"
                        value=""
                        class="fcon-fix-amount__input"    
                        id="<?php echo esc_attr($id); ?>"
                    >
                    <label for="<?php echo $id;?>" class="fcon-fix-amount__label">
                        <span class="fcon-fix-amount__num" x-text="fix_variants[<?php echo $i;?>]"></span>
                        <dfn><?php echo esc_html( $currency );?></dfn>
                    </label>
                </div>

            <?php } ?>

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
                    placeholder="<?php _e('Your amount', 'fcon');?>">
            </div>

            <div
                x-show="flex_error"
                x-cloak
                class="fcon-amount__error fcon-field-error">Укажите сумму между <span x-text="min"></span> и <span x-text="max"></span><dfn><?php echo esc_html($currency);?></dfn></div>
        </div>
    <?php
    }

} // trait
