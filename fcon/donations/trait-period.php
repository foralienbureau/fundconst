<?php if( !defined('WPINC') ) die;

trait Fcon_Dform_Period {

    protected $default_period;

    abstract public function get_form_id();

    abstract public function get_landing();


    protected function get_default_period() {

        if ( $this->default_period === null ) {
            
            $landing = $this->get_landing();

            $this->default_period = $landing->get_default_period();
        }

        return $this->default_period;
    }

    protected function has_period() {

        $landing = $this->get_landing();

        return $landing->has_recurring_support();
    }

    protected function period() {

        $form_id = $this->get_form_id();
    ?>
    <div
        x-data="{}"
        class="fcon-period">
        <div class="fcon-period__cell">
            <?php $id_single = sprintf( "%s-period-single", $form_id ); ?>
            <input
                x-model="$store.landingData.period"
                type="radio"
                name="period_type" 
                value="single" 
                id="<?php echo esc_attr( $id_single );?>"
                class="fcon-period__input">
            <label for="<?php echo esc_attr( $id_single );?>" class="fcon-period__label">Разово</label>
        </div>

        <div class="fcon-period__cell">
            <?php $id_recurring =  sprintf("%s-period-recurring", $form_id );?>
            <input 
                x-model="$store.landingData.period"
                type="radio" 
                name="period_type" 
                value="recurring" 
                id="<?php echo esc_attr( $id_recurring );?>"
                class="fcon-period__input">
            <label for="<?php echo esc_attr( $id_recurring );?>" class="fcon-period__label">Ежемесячно</label>
        </div>
    </div>
    <?php
    }


} // trait
