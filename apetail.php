<?
/*
Plugin Name: ApeTail
Plugin URI: https://fundaria.com/ApeTail
Description: Custom public chats under posts, private and closed group chats. Stream with filters, direct replies with sub-context flows. Advanced communications for a web project.
Version: 1.0.0
Author: Olexiy Ayahov
License: GPLv2 or later
Text Domain: apetail
*/
if(!defined('ABSPATH')){
    die;
}

class ApeTail {
    var $defaulButtonSettingsObject = 
"
{
  buttonPosition: 'bottomleft',
  buttonText:'CHAT',
  buttonCss: {
    border: '3px solid #787284',
    'border-radius': '20px',
    padding: '0 10px',
    background: '#B4AAC5',
    color: '#FFF',
    'font-weight': 'bold',
    'text-shadow':'1px 1px 1px #787284'
  }    
}";
    var $defaultUnderPostsObject = 
"
{
  //chatNameAlias: 'Article context discussion',
  //pubName: 'Main public room'
}";

    function __construct(){
        add_action('wp_enqueue_scripts',[$this,'enqueue_wp']);
        add_action('admin_menu', [$this,'add_admin_menu']);
        add_filter('plugin_action_links_'.plugin_basename(__FILE__), [$this,'plugin_setting_link']);
        add_action('admin_init',[$this,'settings_init']);
        add_action('admin_head', [$this,'admin_style']);
        add_action('wp_head',[$this,'apetail_js_object']);         
        add_filter('the_content',[$this,'add_apetail_under_post']);
        add_filter('widgets_init', [$this,'unregister_recent_comments_widget']);
        add_filter('widget_block_content', [$this,'remove_recent_comment_from_widget_block'], 10, 3 );                       
    }
    function unregister_recent_comments_widget(){
        unregister_widget('WP_Widget_Recent_Comments');     
    }
    function remove_recent_comment_from_widget_block( $content, $instance, $widget ){
    	return strpos($content,'wp-block-latest-comments')?null:$content;
    }      
    function admin_style(){
        echo 
"<style>
    #button_settings_object{min-width:300px;min-height:300px;}
    #under_posts_object{min-width:300px;min-height:150px;}
    .apetail-notice{background: #fff;border: 1px solid #c3c4c7;border-left-width: 4px;box-shadow: 0 1px 1px rgba(0,0,0,.04);margin:0;padding: 1px 12px;}
    .apetail-notice.warning{border-left-color: #dba617;}
    .apetail-notice.info{border-left-color: #72aee6;}
    a.big{display:block;margin:20px;font-size:170%;margin-left:0;}            
</style>";
    }
    
    function get_host_name($options){
        $hostName = @$options['hostname'];
        if(!$hostName) {
            $parts = explode('.',$this->get_domain($_SERVER['SERVER_NAME']));
            $hostName = ucfirst($parts[0]);
        }
        return $hostName;        
    }
            
    function apetail_js_object(){
        $options = get_option('apetail_settings_options');
        $checked = 'checked';
        if(!isset($options['show_button'])&&!empty($options))$checked='';
        if(!$checked)return; 
        $hostName = $this->get_host_name($options);
        $hostName = str_replace(["'","\\"],["&quot;","\\\\"],$hostName);
        $buttonSettingsObject = @$options['button_settings_object'];
        if(!$buttonSettingsObject) $buttonSettingsObject = $this->defaulButtonSettingsObject;       
        $pos = strpos($buttonSettingsObject, '{');
        if($pos !== false) {
            $buttonSettingsObject = substr_replace($buttonSettingsObject, "{hostName:'$hostName',", $pos, 1);
        }        
        echo "<script>var ApeTail = $buttonSettingsObject</script>";        
    }
    function enqueue_wp(){
        wp_enqueue_script('ApeTailWpScript', 'https://fundaria.com/AT/js/init.js');
    }
    function add_admin_menu(){
        add_menu_page(
            esc_html__('ApeTail Settings Page', 'apetail'),
            esc_html__('ApeTail','apetail'),
            'manage_options',
            'apetail_settings',
            [$this, 'apetail_admin_page'],
            'dashicons-admin-comments',
            100
        );
    }
    function apetail_admin_page(){
        require_once plugin_dir_path(__FILE__).'admin/admin-page.php';
    }
    function plugin_setting_link($links){
        $custom_link = '<a href="admin.php?page=apetail_settings">'.esc_html__('Settings','apetail').'</a>';
        array_push($links, $custom_link);
        return $links;        
    }
    function settings_init(){
        register_setting('apetail_settings','apetail_settings_options');
        add_settings_section('settings_section', __('','apetail'), [$this, 'settings_section_html'], 'apetail_settings');
        add_settings_field('hostname', esc_html__('Host name','apetail'), [$this, 'hostname_html'], 'apetail_settings', 'settings_section');
        add_settings_field('show_button', esc_html__('Show trigger button','apetail'), [$this, 'show_button_html'], 'apetail_settings', 'settings_section');
        add_settings_field('button_settings_object', esc_html__('Trigger button settings JavaScript object','apetail'), [$this, 'button_settings_object_html'], 'apetail_settings', 'settings_section');
        add_settings_field('put_chat_rooms_under_posts', esc_html__('Put chat rooms under posts instead default comments','apetail'), [$this, 'put_chat_rooms_under_posts_html'], 'apetail_settings', 'settings_section');
        add_settings_field('under_posts_object', esc_html__('Chat room under posts JavaScript object','apetail'), [$this, 'button_js_html'], 'apetail_settings', 'settings_section');    
    }
    
    function settings_section_html(){
        if(!get_option('apetail_settings_options')){
        ?>
          <div class="apetail-notice warning">
              <p><?php _e( "After activation, to become the main admin of the host, sign in/sign up on ApeTail (not as guest) and use the command <b>#iamadmin</b>. Try to do it immediately (just now) :-)", 'apetail' ); ?></p>
          </div>            
        <?php }
    }
    
    function hostname_html(){
        $options = get_option('apetail_settings_options'); #print_r($options);
        $hostName = @$options['hostname'];
        if(!$hostName) {
            $parts = explode('.',$this->get_domain($_SERVER['SERVER_NAME']));
            $hostName = ucfirst($parts[0]);
        }    
        ?>
        <input type="text" maxlength=32 name="apetail_settings_options[hostname]" value="<?php esc_attr_e($hostName)?>" />
    <?php }
    
    function show_button_html(){
        $options = get_option('apetail_settings_options');        
        $checked = 'checked';
        if(!isset($options['show_button'])&&!empty($options))$checked='';  
        ?> <input type="checkbox" name="apetail_settings_options[show_button]" <?php esc_attr_e($checked);?> />
    <?php }
    
    function button_settings_object_html(){
        $options = get_option('apetail_settings_options'); ?>
        <textarea id="button_settings_object" name="apetail_settings_options[button_settings_object]">
            <?php echo esc_textarea(isset($options['button_settings_object']) ? $options['button_settings_object'] : $this->defaulButtonSettingsObject);  ?>
        </textarea>
        <div class="apetail-notice info">
            <p><?php _e( "<b>buttonPosition</b> (required) where on the page trigger button is fixed. Available values: <i>bottomleft</i>, <i>bottomright</i>, <i>topleft</i>, <i>topright</i><br>
<b>buttonText</b> (optional) the default text on the trigger button<br>
<b>buttonCss</b> (optional) CSS styles applied on the trigger button. If a style name has dash, use single quotes. If not used, default style is applied. The precise position on the page may be changed with <i>top</i>,<i>right</i>,<i>bottom</i> and <i>left</i> with string values like <i>'10px'</i>, where <i>px</i> means pixels<br>
<b>pubName</b> (optional) name of the main public chat used instead default name (\"Pub\" in english)<br>
<b>chatName</b> (optional) name of the custom chat (new or existed) where user will be directed (if not mentioned, the main public room will be used by default)<br>
<b>directToPrivate</b> (optional) nickname of the user to whose private room an entering user will be directed<br>
<b>privateAlias</b> (optional) the text on the button, if <b><i>directToPrivate</i></b> is used, which placed instead nickname (for example it can be \"Support\")<br>
<font color='grey'><b>Rules for JavaScript object</b> 1) It wrapped in curly braces <b>{}</b> 2) It consists from name:value pairs separated by colons 3) name:value pairs separated by comma 4) Name is taken in quotes if has not latin characters, dollar sign <b>\$</b> or underscore <b>_</b> 5) Value is taken in quotes if it is a string type 6) Value can be another JavaScript object 7) Single-string name:value pair can be temporary disabled by \"commenting\" with double slashes <b>//</b> on the single-string begining 8) Single quote <b>'</b> and separete slash <b>\</b> should be escaped with slash -> \' and \\\\ respectivelly</font>", 'apetail' ); ?></p>
        </div>
    <?php }
    
    function put_chat_rooms_under_posts_html(){
        $options = get_option('apetail_settings_options');        
        $checked = 'checked';
        if(!isset($options['put_chat_rooms_under_posts'])&&!empty($options))$checked='';  
        ?> <input type="checkbox" name="apetail_settings_options[put_chat_rooms_under_posts]" <?php esc_attr_e($checked);?> />
    <?php }
    
    function button_js_html(){
        $options = get_option('apetail_settings_options'); ?>
        <textarea id="under_posts_object" name="apetail_settings_options[under_posts_object]">
            <?php echo esc_textarea(isset($options['under_posts_object']) ? $options['under_posts_object'] : $this->defaultUnderPostsObject);  ?>
        </textarea>
        <div class="apetail-notice info">
            <p><?php _e( "<b>chatNameAlias</b> (optional) the chat name replacer on the top tab. The same name alias for any post page chat room. If not used, the name of a chat room displayed as post's title.<br>
<b>pubName</b> <i>see description above</i><br>
<b>buttonCss</b> <i>see description above</i><br>
<i color='grey'>other options are not recommended here</i> 
", 'apetail' ); ?></p>
        </div>        
    <?php }
    
    function add_apetail_under_post($content){
        global $post;
        $options = get_option('apetail_settings_options');
        $checked = 'checked';
        if(!isset($options['put_chat_rooms_under_posts'])&&!empty($options))$checked='';
        if(!$checked)return;
        
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
        add_filter('comments_array', '__return_empty_array', 10, 2);
        $containerId = 'apetail-container'; 
        $content.="<div id='$containerId'></div>";
        
        $hostName = $this->get_host_name($options);
        if(isset($options['under_posts_object']))$underPostsObject = $options['under_posts_object'];
        else $underPostsObject = $this->defaultUnderPostsObject;
        $hostName = str_replace(["'","\\"],["&quot;","\\\\"],$hostName);
        $chatName = str_replace(["'","\\"],["&quot;","\\\\"],$post->post_title);  
        $pos = strpos($underPostsObject, '{');
        if ($pos !== false) {
            $underPostsObject = substr_replace($underPostsObject, "{hostName:'$hostName',chatName:'$chatName',containerId:'$containerId',", $pos, 1);
        } 
        $content.= "<script>var ApeTail = $underPostsObject</script>";                   
        return $content;
    }    
    function get_domain($url){
        $domain = strpos($url,'/')? parse_url($url, PHP_URL_HOST):$url;
        $ccTLD = json_decode( file_get_contents(plugin_dir_url( __FILE__).'admin/ccTLD.json'), 1 );
        $parts = explode('.',$domain);
        if(count($parts)>3) $parts = array_slice($parts, -3);
        if(count($parts)==3&&!in_array($parts[2],$ccTLD)) $parts = array_slice($parts, -2);
        return implode('.',$parts); 
    }                        
}
new ApeTail();
