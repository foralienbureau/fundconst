<?php if( !defined('WPINC') ) die;

interface Fcon_Dform_Settings {

    public function get_form_id();

    public function get_form_title();

    public function get_landing();

    public function get_currency_label();

    public function get_currency_code();

    public function get_success_url();
}


abstract class Fcon_Dform_Base implements Fcon_Dform_Settings {

    use Fcon_Dform_Title, Fcon_Dform_Period,
        Fcon_Dform_Simple_Amount, Fcon_Dform_Natural_Amount,
        Fcon_Dform_Inputs, Fcon_Dform_Agree, Fcon_Dform_Agree_News;

    protected $landing;

    protected $button_text;

    protected $form_title;

    protected $form_id;

    protected $css_base = 'fcon-form';

    
    public function __construct( $donationform ) {

        $this->landing      = $donationform['landing'];
        $this->button_text  = $donationform['button_text'];
        $this->form_title   = $donationform['form_title'];
        $this->form_id      = uniqid("fcon-{$this->landing->ID}-");
    }


    abstract public function print_markup();


    // Fcon_Dform_Settings interface
    public function get_form_id() {

        return $this->form_id;
    }

    public function get_form_title() {

        return $this->form_title;
    }

    public function get_landing() {

        return $this->landing;
    }

    public function get_currency_label() {

        return $this->landing->get_currency_label();
    }

    public function get_currency_code() {

        return $this->landing->get_currency_code();
    }

    public function get_success_url() {

        return $this->landing->get_success_url();
    }

    // mix
    protected function get_amount_limits() {

        return $this->landing->get_amount_limits();  
    }

    protected function get_target_type() {

        return $this->landing->get_target_type();
    }

    protected function get_amount_default( $period ) {

        if( $period == 'recurring' ) {
            return $this->landing->get_recurring_default_amount();
        }
        else {
            return $this->landing->get_single_default_amount();
        }
    }

    protected function get_amount_variants( $period ) {

        if( $period == 'recurring' ) {
            return $this->landing->get_recurring_amount_variants();
        }
        else {
            return $this->landing->get_single_amount_variants();
        }
    }

    protected function get_pm_settings() {

        return $this->landing->get_pm_settings();
    }

    protected function get_nonce() {

        $nonce_disabled = $this->landing->is_form_nonce_disabled();

        if( !$nonce_disabled ) {
            return wp_create_nonce( 'leyka_payment_form' );
        }
        
        return false;
    }

    protected function get_campaign_id() {

        return $this->landing->get_campaign_id();
    }

    protected function get_payment_purpose() {

        return $this->landing->get_payment_purpose();
    }


    // parts  
    protected function form_submit() {
    ?>
        <button type="submit" class="fcon-donate-button">
            <span><?php echo esc_html($this->button_text);?></span></button>
    <?php
    }

    protected function form_error_message() {
    ?>
        <div
            x-show="$store.landingData.has_form_error"
            :class="{'visible': $store.landingData.has_form_error}"
            x-cloak 
            class="fcon-form-error">
            <span class="fcon-form-error__text" x-html="$store.landingData.form_error_message"></span>
        </div>
    <?php
    }

    protected function amount() {

        $target_type = $this->get_target_type();

        if( $target_type == 'natural' ) {

            $this->natural_amount_selection();
        }
        else {

            $this->simple_amount_selection();
        }
    }

    
    // form scripts helper
    protected function print_shared_store_js() {

        if ( !fcon()->is_registered_printed_js( 'landingData' ) ) {

            $this->form_shared_store();

            fcon()->register_printed_js( 'landingData' );
        }
    }

    protected function print_form_base_js() {

        if ( !fcon()->is_registered_printed_js( 'fconDformBase' ) ) {   
            
            $this->form_base_js();

            fcon()->register_printed_js( 'fconDformBase' );
        }
    }


    // data to JS
    protected function form_shared_store() {

        $landing_data = array(
            'payment_methods' => $this->get_pm_settings(),
            'active_pm' => '',
            'target_type' => $this->get_target_type(),
            'period' => $this->get_default_period(),

            'amount_variants' => array(),
            'default_amount' => array(),
            'amount_limits' => array(),

            'amount_selection_type' => 'fix',
            'amount' => 0,

            'natural_price' => 0,
            'natural_selection' => 0,

            'name' => '',
            'email' => '',
            'agree' => 0,
            'agree_news' => 0,

            'has_form_error' => false,
            'form_error_message' => '',
        );

        if( $landing_data['target_type'] == 'natural' ) {
            $landing_data['natural_price'] = $this->landing->get_natural_price();
            $landing_data['natural_selection'] = $this->landing->get_natural_selection();
        }
        else {
            $landing_data['amount_variants']['single'] = $this->get_amount_variants( 'single' );
            $landing_data['amount_variants']['recurring'] = $this->get_amount_variants( 'recurring' );
            $landing_data['default_amount']['single'] = $this->get_amount_default( 'single' );
            $landing_data['default_amount']['recurring'] = $this->get_amount_default( 'recurring' );
        }

        $landing_data['amount_limits'] = $this->get_amount_limits();

    ?>
    <script id="landingData">
        document.addEventListener('alpine:init', () => {
            Alpine.store('landingData', {
                <?php echo trim(json_encode($landing_data, JSON_PRETTY_PRINT), "{}") . ","; ?>
                onPeriodChange: function( value ) {

                    if ( 'undefined' === typeof value) {
                        value = this.period;
                    }

                    // amount selection
                    if( this.amount_selection_type == 'fix' ) {

                        if ( this.target_type == 'natural' ) {

                            this.amount = this.natural_selection * this.natural_price;
                        } 
                        else {
                            this.amount = this.default_amount[value];
                        }
                    }

                    // payment methods
                    if( this.payment_methods.hasOwnProperty(value) ) {
                        this.active_pm = this.payment_methods[value];
                    }
                },
                init: function() {          
                    this.onPeriodChange();
                }
            });
        });
    </script>
    <div
        x-data="{invisible: true}"
        x-init="$watch('$store.landingData.period', function(value){$store.landingData.onPeriodChange(value)});" 
        x-show="invisible"><!-- store watcher --></div>
    <?php
    }


    // form JS common logic
    protected function form_base_js() {

        $currency = $this->get_currency_code();

        $leyka = array(
            'leyka_template_id'         => 'need-help',
            'leyka_amount_field_type'   => 'custom',
            'leyka_honeypot'            => '', // how to implement?
            'leyka_campaign_id'         => $this->get_campaign_id(), // id кампании
            'leyka_ga_campaign_title'   => $this->get_payment_purpose(), // основание платежа
            'leyka_agree_pd'            => 1,

            'leyka_donation_currency'   => $currency,

            // from store
            'leyka_payment_method'      => '', // yandex-yandex_all / yandex-yandex_card 
            'leyka_recurring'           => 0, //  0/1
            'leyka_donation_amount'     => 0,
            'leyka_donor_name'          => '',
            'leyka_donor_email'         => '',
            'leyka_agree'               => 0, // 1/0
            'leyka_donor_subscribed'    => 0, // 1/0
        );


        // min / max 
        $limits = $this->get_amount_limits();
        $leyka['top_'.$currency] = $limits['max'];
        $leyka['bottom_'.$currency] = $limits['min'];


        // nonce
        $nonce = $this->get_nonce();

        if( $nonce !== false ) {
            $leyka['_wpnonce'] = $nonce;
        }

        $leyka_data = array( 'leyka' => $leyka );
    ?>
    <script id="fconDformBase">
        const fconDformBase = {
            <?php echo trim(json_encode($leyka_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "{}") . ","; ?>
            loading: false,
            ajax_url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
            success_url: "<?php echo $this->get_success_url();?>",
            general_error: "Произошла ошибка. Проверьте данные формы и попробуйте снова",

            init: function() {

            },

            regularSubmit: function( $dispatch ) {

                if ( this.loading ) {
                    return;
                }

                // validate data in store 
                const valid = this.validateForm( $dispatch );

                if( !valid ) {
                    this.setFormError( 'Форма содержит ошибки. Проверьте ваши данные и попробуйте снова.' );
                    return false;
                }

                // build Leyka compatible data
                const leykaForm = this.buildLeykaForm();

                // request 
                this.makeRequest( leykaForm )
            },

            makeRequest: function( leykaForm ) {
                // ajax 
                this.loading = true;

                var active_pm = this.$store.landingData.active_pm;

                fetch(this.ajax_url, {
                    method: 'POST',
                    headers: {},
                    body: leykaForm
                })
                .then(response => response.json())
                .then((data) => {
                    
                    if( !data || typeof data.status === 'undefined' ) {
                        console.log('dformBase: Empty response');

                        this.setFormError();
                        return false;
                    }
                    else if( data.status !== 0 && typeof data.message !== 'undefined') {
                        console.log('dformBase: ' + data.message);

                        this.setFormError( data.message );
                        return false;
                    }
                    else if( !data.public_id && !data.payment_url )  {
                        console.log( 'dformBase: ' + data.message );
                        this.setFormError( 'Платежные системы не настроены' );
                        return false;
                    }
                    
                    if( data.hasOwnProperty('submission_redirect_type') && data.submission_redirect_type === 'redirect') {
                        
                        this.redirectPaymentHander( data ); // YKassa
                    }
                    else if( active_pm == 'cp-card') {

                        this.cpHandler( data );
                    }

                    // add other pm logic here
                   
                })
                .catch((error) => {
                    console.log(error); 
                    // handle error 
                    this.setFormError()
                })
                .finally(() => {
                    this.loading = false;
                });
            },

            setFormError: function ( message = null ) {

                if( !message ) {
                    message = this.general_error;
                }

                this.$store.landingData.form_error_message = message;
                this.$store.landingData.has_form_error = true;
            },

            redirectPaymentHander: function( data ) {

                // some actions ?
                window.location.href = data.payment_url;
            },

            validateForm: function( $dispatch ) {

                let valid = true;

                const amount = this.$store.landingData.amount;
                const name = this.$store.landingData.name;
                const email = this.$store.landingData.email;

                const min = this.$store.landingData.amount_limits['min'];
                const max = this.$store.landingData.amount_limits['max'];

                if( this.$store.landingData.agree !== 1 ) {
                    valid = false;
                    $dispatch('dform-invalid', {'field': 'agree'});
                }

                if( isNaN( amount ) || !isAmountValid( amount, min, max ) ) {
                    valid = false;
                    $dispatch('dform-invalid', {'field': 'amount'});
                }

                if ( !isTextValid(name) ) {
                    valid = false;
                    $dispatch('dform-invalid', {'field': 'name'});
                }

                if ( !isEmailValid(email) ) {
                    valid = false;
                    $dispatch('dform-invalid', {'field': 'email'});
                }

                return valid;
            },

            leykaSync: function() {

                this.leyka['leyka_payment_method'] = this.$store.landingData.active_pm;

                if( this.$store.landingData.period == 'recurring' ) {
                    this.leyka['leyka_recurring'] = 1;
                }

                this.leyka['leyka_donation_amount'] = this.$store.landingData.amount;
                this.leyka['leyka_donor_name'] = this.$store.landingData.name;
                this.leyka['leyka_donor_email'] = this.$store.landingData.email;
                this.leyka['leyka_agree'] = this.$store.landingData.agree;
                this.leyka['leyka_donor_subscribed'] = this.$store.landingData.agree_news;
            },

            buildLeykaForm: function() {

                // build request body
                const form = new FormData();

                form.append('action', 'leyka_ajax_get_gateway_redirect_data');

                this.leykaSync();
                
                for (const field_name in this.leyka) {
                    form.append(field_name, this.leyka[field_name]);
                }

                return form;
            },

            cpHandler: function( data ) {

                var _ = this;

                var widget = new cp.CloudPayments({language: "ru-RU"}), 
                    widgetData = {},
                    period = _.$store.landingData.period,
                    success_url = _.success_url;


                if( period == 'recurring' ) {
                    widgetData.cloudPayments = {recurrent: {interval: 'Month', period: 1}};
                }

                widget.charge({
                    publicId: data.public_id,
                    description: decodeHtmlentities(data.payment_title),
                    amount: parseFloat(data.amount),
                    currency: data.currency,
                    invoiceId: parseInt(data.donation_id),
                    accountId: data.donor_email,
                    data: widgetData
                }, function(options) { // success callback

                    console.log({'options': options });
                    console.log({'success_url': success_url });

                    _.$store.landingData.has_form_error = false;
                    window.location.href = success_url;
                    
                }, function(reason, options) { // fail callback

                    console.log(reason);
                    console.log(options);
                    // TODO return correct text
                    _.setFormError( 'Оплата не была завершена' );
                });
            },
        }
    </script>
    <?php
    }

} // class 
