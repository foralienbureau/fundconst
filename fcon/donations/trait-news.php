<?php if( !defined('WPINC') ) die;

trait Fcon_Dform_Agree_News {

    abstract public function get_form_id();

    abstract public function get_landing();


    protected function get_agree_news_text() {

        return "Хочу получать новости сайта";
    }
    
    protected function agree_news_checkbox_js() {
    ?>
    <script id="agreeNewsField">
        function agreeNewsField() {
            return {

                init: function() {},

                onFieldChange: function( event ) {

                    if( !event.target.checked ) {
                        this.$store.landingData.agree_news = 0;
                    }
                    else {
                        this.$store.landingData.agree_news = 1;
                    }
                },
            }
        }
    </script>
    <?php
    }

    protected function agree_news_checkbox() {

        if ( !fcon()->is_registered_printed_js( 'agreeNewsField' ) ) {

            $this->agree_news_checkbox_js();

            fcon()->register_printed_js( 'agreeNewsField' );
        }

        $form_id = $this->get_form_id();

        $id = sprintf( "%s-agree-news", $form_id );

        $agree = $this->get_agree_news_text();
    ?>
        <div
            x-data="agreeNewsField"
            class="fcon-agree-news">
            <input 
                @change="onFieldChange"
                type="checkbox"
                name="agree_news"
                class="fcon-agree-news__input"
                value="1"
                id="<?php echo esc_attr( $id ); ?>"
                tabindex="0"
            >
            <label for="<?php echo esc_attr( $id ); ?>" class="fcon-agree-news__label">
                <?php echo esc_html( $agree ); ?></label>
        </div>
    <?php
    }

} // trait
