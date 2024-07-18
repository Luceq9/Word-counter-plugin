<?php

/*
    plugin name: Words Counter
    plugin URI: http://www.myplugin.com
    Description: Word counter plugin for Wordpress that counts words, characters and read time of the post.
    Version: 1.0
    author: Luceq
    Text Domain: wcpdomain
    Domain Path: /languages
*/

class WordCounter {
    function __construct(){
        add_filter('admin_menu', array($this, 'word_count'));
        add_action('admin_init', array($this, 'settings'));
        add_action('the_content', array($this, 'content'));
        add_action('init', array($this, 'languages'));
    }

    function languages(){
        load_plugin_textdomain('wcpdomain', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    function content($content){
        if((is_single() AND is_main_query()) AND get_option('wcp_words_checkbox', '1') OR get_option('wcp_characters_checkbox', '1') OR get_option('wcp_readTime_checkbox', '1')){
          return $this->getHtml($content);
        }
        return $content;
    }

    function getHtml($content) {
        $html = '<h3>' . get_option('wcp_headline', 'Tytuł'). '</h3></br>';
        if(get_option('wcp_words_checkbox', '1')){
            $wordCount = str_word_count(strip_tags($content));
             $html .= esc_html__('Ten post posiada słów:', 'wcpdomain'). ' ' . $wordCount  .'</br>';
        }
        if(get_option('wcp_characters_checkbox', '1')){
            $charCount = strlen(strip_tags($content));
            $html .=  esc_html__('Ten post posiada liter:', 'wcpdomain') . $charCount . '</br>';
        }
        if(get_option('wcp_readTime_checkbox', '1')){
            $readTime = round(str_word_count(strip_tags($content)) / 200);
            if($readTime == 0){
                $readTime = 1;
            }
            
            $html .= esc_html__('Czas czytania tego posta to:', 'wcpdomain') . ' ' . $readTime . ' ' . esc_html__('minut', 'wcpdomain') . ' ' . '</br>';
        }
        if(get_option('word_counter', '0') == '0'){
            return $html . $content;
        } else {
            return $content . $html;
        }
    }

    function settings(){
        add_settings_section('word_counter_section', null, null, 'word-counter');

        add_settings_field('word_counter', 'Pozycja', array($this, 'locationHTML'), 'word-counter', 'word_counter_section');
        register_setting('word_counter_section', 'word_counter', array('sanitize_callback' => array($this, 'sanitaze_location'), 'default' => '0'));

        add_settings_field('wcp_headline', 'Tytuł', array($this, 'headlineHTML'), 'word-counter', 'word_counter_section');
        register_setting('word_counter_section', 'wcp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Tytuł'));

        add_settings_field('wcp_words_checkbox', 'Licznik słów', array($this, 'checkboxHTML'), 'word-counter', 'word_counter_section', array('theName' => 'wcp_words_checkbox'));
        register_setting('word_counter_section', 'wcp_words_checkbox', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        add_settings_field('wcp_characters_checkbox', 'Licznik liter', array($this, 'checkboxHTML'), 'word-counter', 'word_counter_section', array('theName' => 'wcp_characters_checkbox'));
        register_setting('word_counter_section', 'wcp_characters_checkbox', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        add_settings_field('wcp_readTime_checkbox', 'Licznik czasu czytania', array($this, 'checkboxHTML'), 'word-counter', 'word_counter_section' , array('theName' => 'wcp_readTime_checkbox'));
        register_setting('word_counter_section', 'wcp_readTime_checkbox', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
    }

    function sanitaze_location($input){
        if($input == '0' || $input == '1'){
            return $input;
        } else {
            add_settings_error('word_counter', 'word_counter_error', 'Nieprawidłowa wartość', 'error');
        }
    }

    function checkboxHTML($args){
        ?>
        <input type="checkbox" name="<?php echo esc_attr($args['theName']); ?>" value="1" <?php checked(get_option($args['theName']), '1'); ?>>
        <?php
    }

    function headlineHTML(){
        ?>
        <input type="text" name="wcp_headline" value="<?php echo get_option('wcp_headline'); ?>">
        <?php
    }

    function locationHTML(){
       ?>
        <select name='word_counter'>
            <option <?php selected(get_option('word_counter'), '0') ?> value='0'>Na początku posta</option>
            <option <?php selected(get_option('word_counter'), '1') ?> value='1'>Na końcu posta</option>
        </select>
        <?php
    }

    function word_count()
    {
        add_options_page('Word Counter', 'Word Counter', 'manage_options', 'word-counter', array($this, 'word_counter_options'));
    }

    function word_counter_options()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        ?>
        <div class="wrap">
            <h2>Word Counter Settings</h2>
            <form action="options.php" method="post">
                <?php
                settings_fields('word_counter_section');
                do_settings_sections('word-counter');
                submit_button();
                ?>
            </form>
            </div>
        <?php
    }
}


$newWordCounter = new WordCounter();
