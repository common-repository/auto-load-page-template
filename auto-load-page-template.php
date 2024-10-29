<?php
/*
Plugin Name: Auto Load Page Template
Plugin URI: http://www.kigurumi.asia
Description: If this plug-in is enabled, and there is a file on the same theme level as the static page URL level, then that theme file will automatically be loaded as the template file.
Author: Nakashima Masahiro
Version: 2.0.1
Author URI: http://www.kigurumi.asia
License: GPLv2 or later
Text Domain: alt
Domain Path: /languages/
 */
define('ALPT_VERSION', '2.0.1');
define('ALPT_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('ALPT_PLUGIN_NAME', trim(dirname(ALPT_PLUGIN_BASENAME), '/'));
define('ALPT_PLUGIN_DIR', untrailingslashit(dirname(__FILE__)));
define('ALPT_PLUGIN_URL', untrailingslashit(plugins_url('', __FILE__)));
define('ALPT_TEXT_DOMAIN', 'ALPT');

class Auto_Load_Page_Template
{
    // デフォルトオプション
    private $default_options = array(
        'can_push_post' => true,
    );

    public function __construct()
    {
        //管理画面
        // include_once ALPT_PLUGIN_DIR . '/classes/class.admin.php';
        //固定ページの読み込むテンプレートを変更
        add_filter('page_template', array($this, 'get_page_template'));
        add_filter('home_template', array($this, 'get_page_template'));
        // プラグインが有効・無効化されたとき
        register_activation_hook(__FILE__, array($this, 'activation_hook'));
        register_deactivation_hook(__FILE__, array($this, 'deactivation_hook'));
        // register_uninstall_hook(__FILE__, array($this, 'uninstall_hook'));
    }

    /**
     * 固定ページテンプレートをロードする
     */
    public function get_page_template($template)
    {
        global $wp_query;
        global $post;

        if ($wp_query->is_page == 1) {
            //パスを作成
            $home_url      = get_home_url();
            $permalink     = get_permalink($post->ID);
            $template_path = str_replace($home_url, "", $permalink);

            //子テーマ
            $child_template_static_path = get_stylesheet_directory() . '/static/' . $template_path . 'index.php';
            $child_template_path = get_stylesheet_directory() . $template_path . 'index.php';
            //親テーマ
            $parent_template_static_path = get_template_directory() . '/static/' . $template_path . 'index.php';
            $parent_template_path = get_template_directory() . $template_path . 'index.php';

            //テンプレートファイルがあるかどうかを子テーマを優先して調べる
            if (file_exists($child_template_static_path)) {
                $template = $child_template_static_path;
            } elseif (file_exists($child_template_path)) {
                $template = $child_template_path;
            } elseif (file_exists($parent_template_static_path)) {
                $template = $parent_template_static_path;
            } elseif (file_exists($parent_template_path)) {
                $template = $parent_template_path;
            }
        }
        return $template;
    }

    /**
     * 指定したhtmlを本文に保存する
     */
    public function push_post($attr)
    {
        // 保存できるかどうかをチェックする
        $options = get_option(ALPT_PLUGIN_NAME);
        if (!$options['can_push_post']) {
            return;
        }

        include_once ALPT_PLUGIN_DIR . '/libs/simple_html_dom.php';

        // 固定ページのHTMLを取得
        $template = $this->get_page_template(false);
        if ($template) {
            $html = file_get_html($template);
            // 属性のテキストを取得
            $result = '';
            foreach ($html->find($attr) as $data) {
                $result .= $data;
            }

            //データを本文に保存する
            global $post;
            $post = array(
                'ID'           => $post->ID,
                'post_content' => $result,
            );
            wp_update_post($post);

            return $result;
        }

        return false;
    }

    /**
     * プラグインが有効化されたときに実行
     */
    public function activation_hook()
    {
        if (!get_option(ALPT_PLUGIN_NAME)) {
            update_option(ALPT_PLUGIN_NAME, $this->default_options);
        }
    }

    /**
     * 無効化ときに実行
     */
    public function deactivation_hook()
    {
        delete_option(ALPT_PLUGIN_NAME);
    }

    /**
     * アンインストール時に実行
     */
    public function uninstall_hook()
    {
        delete_option(ALPT_PLUGIN_NAME);
    }
}
global $auto_load_page_template;
$auto_load_page_template = new Auto_Load_Page_Template();
