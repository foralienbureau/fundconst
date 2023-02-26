<?php if( !defined('WPINC') ) die;

class Fcon_Target_Template {

    protected $landing;

    protected $css_base = 'fcon-progress';

    
    public function __construct( $landing ) {

        $this->landing = $landing;
    }

    public function print_markup() {

        if ( !$this->has_target() ) {
            return;
        }

        $type = $this->get_target_type();
        
        $classes   = array($this->css_base);

        $classes[] = "{$this->css_base}--{$type}";
    ?>
    <div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
        
        <div class="<?php echo esc_attr( $this->css_base );?>__scale">
            <?php $this->scale();?>
        </div>

        <div class="<?php echo esc_attr( $this->css_base );?>__label">
            <?php $this->labels();?>
        </div>
    </div>
    <?php
    }

    protected function has_target() {

        return $this->landing->has_target();
    }

    protected function get_target_type() {

        return $this->landing->get_target_type();
    }

    protected function scale() {

        $w = $this->get_scale_progress();

        if( $w < 1 ) {
            $w = 1;
        }

        if( $w > 100 ) {
            $w = 100;
        }
    ?>
        <div class="fcon-scale">
            <div class="fcon-scale__bg"></div>
            <div class="fcon-scale__progress" style="width: <?php echo $w;?>%;"></div>
        </div>
    <?php
    }

    protected function labels() {

        $collected = $this->get_collected_label();

        $needed = $this->get_needed_label();
    ?>
        <div class="fcon-target-labels">
            <div class="fcon-target-labels__collected"><?php echo esc_html( $collected );?></div>
            <div class="fcon-target-labels__needed"><span class="label"><?php _e('Needed', 'fcon');?></span><?php echo esc_html( $needed );?></div>
        </div>
    <?php
    }

    protected function get_scale_progress() {

        $collected = $this->landing->get_collected_amount();

        $target = $this->landing->get_target_amount();

        return round( ( $collected / $target ) * 100 );
    }

    protected function get_collected_label() {

        $raw = (int)$this->landing->get_collected_value();

        if( $raw < 1 ) {
            return 0;
        }

        $value = number_format( $raw , 0, '.', '&nbsp;' );

        $label = $this->landing->get_target_part_label( $raw  );

        return "{$value}&nbsp;{$label}";
    }

    protected function get_needed_label() {

        $raw = (int)$this->landing->get_target_value();

        if( $raw < 1 ) {
            return 0;
        }

        $value = number_format( $raw , 0, '.', '&nbsp;' );

        $label = $this->landing->get_target_part_label( $raw );

        return "{$value}&nbsp;{$label}";
    }


} // class 

class Fcon_Target_Admin_Template extends Fcon_Target_Template {

    public function print_markup() {
    ?>
    <div class="<?php echo esc_attr( $this->css_base );?>">
        
        <div class="<?php echo esc_attr( $this->css_base );?>__target">
            <span class="fcon-key">Цель:</span>
            <span class="fcon-value"><?php echo $this->get_needed_mark();?></span>
        </div>

        <div class="<?php echo esc_attr( $this->css_base );?>__collected">
            <span class="fcon-key">Собрано:</span>
            <span class="fcon-value" style="font-weight: bold;">
                <?php echo $this->get_collected_mark();?>
            </span>
        </div>
    </div>
    <?php
    }

    protected function get_needed_mark() {

        if ( !$this->has_target() ) {
            return "Не установлена";
        }

        return $this->get_needed_label();
    }

    protected function get_collected_mark() {

        if ( !$this->has_target() ) {

            $raw = $this->landing->get_collected_amount_from_campaign();
            $value = number_format( $raw , 0, '.', '&nbsp;' );
            $label = $this->landing->get_currency_label();

            return "{$value}&nbsp;{$label}";
        }

        return $this->get_collected_label();
    }


} // class 
