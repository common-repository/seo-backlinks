<?php
/*
 Plugin Name: SEO Backlinks
 Plugin URI: https://www.viatadecocktail.ro
 Description: This plugin raise the internal links on your site!
 Version: 4.0.1
 Author: Andrei Sebastian - andreisebastian
 Author URI: https://www.viatadecocktail.ro
 Copyright 2020

This program is free software: you can redistribute it and/or modify
it under the terms of the Gnu General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the Gnu General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function loc_insert_links( $text, $is_content = false ){
    $dictionary = get_option('loc_dic');
    if( !is_array($dictionary) ) return $text;
    
    $loc_max = get_option('loc_max');
    $loc_sensitive = get_option('loc_sensitive');
    $loc_target_blank = get_option('loc_target_blank');
    $loc_back = get_option('loc_back');
    
    
    $anysign = ( $loc_max != 'si' ) ? '(\W|\A|\Z)' : '(.|\A|\Z|)';
 
    $target = ( $loc_target_blank == 'si' ) ? ' target="_blank"' : '';
    
    foreach($dictionary as $lbl => $val){
        
        if( substr($val, 0, 7) != 'http://' )
            $val = 'http://'.$val;
        
        $reg = '/'.$anysign.'('.$lbl.')'.$anysign.'/';
        $expr_from[] = ( $loc_sensitive != 'si' ) ? $reg.'i' : $reg;
        $expr_to[] = '$1<a href="'.$val.'"'.$target.'>$2</a>$3';
    }
   
    $html = '';
    $tmp = array();
    $frammenti = explode( '<', $text );
  
    foreach( $frammenti as $cont => $frammento )
    {
        if( strpos( $frammento, '>' ) ){
            $tmp = explode( '>', $frammento );
            $html .= $tmp[0].'>';
            unset( $tmp[0] );
            foreach( $tmp as $parte )
                $html .= preg_replace($expr_from, $expr_to, $parte);
            
        }else{
            $html .= preg_replace($expr_from, $expr_to, $frammento);
        }
        
        if( $cont+1 != count($frammenti) ) 
            $html .= '<';
    }
    
    if( $loc_back == 'Yes' && $is_content )
        $html .= '<div style="clear: both; float: right; font-size: 0.8em;">This site is using SEO Baclinks plugin created by <a href="https://www.viatadecocktail.ro" rel="follow">Cocktail Family</a></div>';
    return $html;
}

function loc_config(){
    $selects = array(
        'loc_max' => array('No','Yes'),
        'loc_sensitive' => array('No','Yes'),
        'loc_target_blank' => array('No','Yes'),
        'loc_back' => array('Yes','No'),
    );    
    
    if( isset($_POST['submitted']) ){
        $n = ceil( count( $_POST ) / 2 );
        for( $k = 0; $k < $n; $k++ ){
            if( $_POST['key_'.$k] != '' ){
                $tmp[$_POST['key_'.$k]] = $_POST['url_'.$k];
            }
        }
        update_option('loc_dic', $tmp);
        foreach( $selects as $lbl => $val )
            update_option($lbl, $_POST[$lbl]);
    }
    
    foreach( $selects as $field => $select ){
        $var_value = get_option($field);
        $sel[$field] = '<select name="'.$field.'">';
        foreach( $select as $option ){
            $selected = ( $option == $var_value ) ? ' selected="selected"' : '';
            $sel[$field] .= '<option value="'.$option.'"'.$selected.'>'.$option.'</option>';
        }
        $sel[$field] .= '</select>';
    }   
    
    $dictionary = get_option('loc_dic');
    
    $html = '
        <div class="wrap">
            <h2>SEO Backlinks</h2>
            <form name="example" method="post">
            
            <table>
                <tr><td>Case sensitive</td><td>'.$sel['loc_sensitive'].'</td><td>Activate?</td></tr>
                <tr><td>Open link in new window</td><td>'.$sel['loc_target_blank'].'</td><td>Do you want to open the links in new wimdow?</td></tr>
                <tr><td>Do you want to give me a backlink?</td><td>'.$sel['loc_back'].'</td><td>The answer is simple YES or NO</td></tr>
            </table>

            <table class="wp-list-table widefat plugins" style="margin-bottom: 10px;">
            <tr><th class="column-name" style="width: 30px;">n</th>
                <th class="column-name" style="width: 250px;">Word</th>
                <th class="column-name">Link</th></tr>';
    $cont = 1;
    if( is_array($dictionary) && count( $dictionary ) > 0 ){
        foreach( $dictionary as $key => $url ){
            $html .= '<tr><td class="row-title">'.$cont.'</td>
                <td><input type="text" name="key_'.$cont.'" value="'.$key.'" /></td>
                <td><input type="text" name="url_'.$cont.'" value="'.$url.'" style="width: 450px;" /></td></tr>';
            $cont++;
        }
    }
    $html .= '  
                <tr><td class="row-title">'.$cont.'</td>
                    <td><input type="text" name="key_'.$cont.'" value="" /></td>
                    <td><input type="text" name="url_'.$cont.'" value=""  style="width: 450px;" /></td></tr>
                <input type="hidden" name="submitted" />
                </table>
                <input type="submit" value="Save Settings" class="button-secondary action" />
            <form>
        </div>
        ';
    $html .= loc_instructions();
    
    echo $html;    
}

function loc_insert_links_comment( $text ){
    return loc_insert_links( $text );
}

function loc_insert_links_content( $text ){
    return loc_insert_links( $text, true );
}

function loc_instructions(){
    $html = '<p style="margin-top: 10px;">If you want to remove a link then you have to blank the fields (word and link) then click the <strong>Save settings</strong> button</p>';
    return $html;
}

function loc_addlink(){
    add_menu_page('SEO Backlinks', 'SEO Backlinks', 'administrator', 'loc_menu', 'loc_config');
}

add_filter('the_content', 'loc_insert_links_content');
add_filter('comment_text', 'loc_insert_links_comment');
add_action('admin_menu', 'loc_addlink');

?>
