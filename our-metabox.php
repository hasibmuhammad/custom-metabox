<?php
/*
Plugin Name: Our Metabox
Plugin URI:  
Description: Our Metabox is responsible for showing metabox of our
Version:     1.0
Author:      Hasib Muhammad
Author URI:  https://developer.wordpress.org/
Text Domain: our-metabox
Domain Path: /languages
License:     GPL2
*/

class NewMetabox{
    public function __construct(){
        add_action('plugins_loaded',array($this,'omb_load_textdomain'));
        add_action('admin_menu',array($this,'omb_add_metabox'));
        add_action('save_post',array($this,'omb_save_post'));
    }

    private function is_secured($nonce_field_name,$action,$post_id){
        
        $nonce = isset($_POST[$nonce_field_name])?$_POST[$nonce_field_name]:'';
        
        if($nonce == ''){
            return false;
        }
        if(!wp_verify_nonce($nonce,$action)){
            return false;
        }
        if(!current_user_can('edit_post',$post_id)){
            return false;
        }
        if(wp_is_post_autosave($post_id)){
            return false;
        }
        if(wp_is_post_revision($post_id)){
            return false;
        }
        return true;
    }

    function omb_save_post($post_id){
        
        if(!$this->is_secured('omb_nonce_field','omb_nonce',$post_id)){
            return $post_id;
        }
        $country = isset($_POST['omb_country'])?$_POST['omb_country']:'';
        $location = isset($_POST['omb_location'])?$_POST['omb_location']:'';
        $is_favorite = isset($_POST['omb_is_favorite'])?$_POST['omb_is_favorite']:0;
        $checked_colors = isset($_POST['omb_color'])?$_POST['omb_color']:array();
        $checked_radio = isset($_POST['omb_radio'])?$_POST['omb_radio']:'';
        $selected_country = isset($_POST['omb_dropdown_country'])?$_POST['omb_dropdown_country']:'';


        if($country == '' || $location == ''){
            return $post_id;
        }
        $country = sanitize_text_field($country);
        $location = sanitize_text_field($location);

        update_post_meta( $post_id, 'omb_country', $country );
        update_post_meta( $post_id, 'omb_location', $location );
        update_post_meta( $post_id, 'omb_is_favorite', $is_favorite );
        update_post_meta( $post_id, 'omb_color',$checked_colors );
        update_post_meta( $post_id, 'omb_radio', $checked_radio );
        update_post_meta( $post_id, 'omb_dropdown_country', $selected_country );
        
    }
    
    function omb_add_metabox(){
        add_meta_box(
            'our-metabox',
            __('Amazing MetaBox','our-metabox'),
            array($this,'omb_metabox_form'),
            'post'
        );
    }

    function omb_metabox_form($post){
        $label1         = __('Country','our-metabox');
        $label2         = __('Location','our-metabox');
        $label3         = __('Is Favorite','our-metabox');
        $label4         = __('Colors:','our-metabox');
        $label5         = __('Radio:','our-metabox');
        $label6         = __('Countries:','our-metabox');
        
        $country        = get_post_meta($post->ID,'omb_country',true);
        $location       = get_post_meta($post->ID,'omb_location',true);
        $is_favorite    = get_post_meta($post->ID,'omb_is_favorite',true);
        $checked_colors = get_post_meta($post->ID,'omb_color',true);
        $checked_radio  = get_post_meta($post->ID,'omb_radio',true);
        $selected_country  = get_post_meta($post->ID,'omb_dropdown_country',true);

        $checked        = $is_favorite == '1' ? 'checked' : '';

        $colors         = array('red','yellow','blue','white','magenta','black');
        $radios         = array('love','hate');
        $countries      = array('Afganistan','Bangladesh','Bhutan','India','Maldives','Nepal','Srilanka','Pakistan');

        wp_nonce_field( 'omb_nonce', 'omb_nonce_field' );

        $html = <<<EOD
<p>
<label for="omb_country">{$label1}</label>
<input type="text" name="omb_country" id="omb_country" value="{$country}"/>
</br>
<label for="omb_location">{$label2}</label>
<input type="text" name="omb_location" id="omb_location" value="{$location}"/>
</p>

<p>
<label for="omb_is_favorite">{$label3}</label>
<input type="checkbox" name="omb_is_favorite" id="omb_is_favorite" value="1" {$checked} />
</p>

<p>
<label>{$label4}</label>

EOD;
        foreach($colors as $color){
            $_color = ucwords($color);
            $checked = in_array($color,$checked_colors)?'checked':'';
            $html .= <<<EOD
<label for="omb_color_{$color}">{$_color}</label>
<input type="checkbox" name="omb_color[]" id="omb_color_{$color}" value="{$color}" $checked/>
EOD;
        }
        $html .= '</p>';
        $html .= <<<EOD
<p>
<label>{$label5}</label>
EOD;
        foreach($radios as $radio){
            $_radio = ucwords($radio);
            $checked = $radio == $checked_radio ? 'checked' : '';
            $html .= <<<EOD

<label for="omb_radio_{$radio}">{$_radio}</label>
<input type="radio" name="omb_radio" id="omb_radio_{$radio}" value="{$radio}" {$checked}/>
EOD;
        }
        $html .='</p>';
         
        $html .= <<<EOD
        
<p>
<label>{$label6}</label>
<select name="omb_dropdown_country" id="omb_dropdown_country">
EOD;
        foreach($countries as $country){
            $selected = $selected_country == $country? 'selected' : '';
            $html .= <<<EOD
<option value="{$country}" {$selected}>{$country}</option>
EOD;
        }
        $html .= '</select>';
        $html .= '</p>';
        echo $html;
    }

    function omb_load_textdomain(){
        load_plugin_textdomain('our-metabox',false,dirname(__FILE__).'/lanugages');
    }
}
new NewMetabox();