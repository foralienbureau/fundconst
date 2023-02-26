<?php if( !defined('WPINC') ) die;
  

class Fcon_Leyka_Campaign {

    protected $campaign;

    public function __construct( $post ) {

        if( is_a( $post, 'Leyka_Campaign') ) {
            $this->campaign = $post;
        }
        else if( is_a( $post, 'WP_Post')  ) {
            $this->campaign = new Leyka_Campaign($post);
        }
        else if( (int)$post > 0 ) {
            $this->campaign = new Leyka_Campaign(get_post((int)$post));
        }
    } 

    
    public function get_id() {

        return $this->campaign->ID;
    }

    public function get_payment_purpose() {

        $text = $this->campaign->payment_title;

        if( !$text ) {
            $text = fcon_get_setting( 'default_purpose' );
        }

        return $text;
    }

    public function get_collected_amount() {

        $collected = (float)$this->campaign->total_funded;

        return round($collected);
    }

    public function get_connection_row_html() {

        $title = apply_filters( 'the_title', $this->campaign->name);
        $date = get_the_date( 'd.m.Y H:s:i', $this->campaign->ID );
        $link = get_edit_post_link( $this->campaign->ID );
        $label = __('Edit', 'fcon');

        $out = "<li>";
        $out .= "<span>{$title}</span>";
        $out .= " - <small style='opacity: 0.7;'>{$date}</small> <a href='{$link}'>{$label}</a>";
        $out .= "</li>";

        return $out;
    }

} // class 
