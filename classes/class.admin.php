<?php
class ALPT_Admin
{

    /**
     * __construct
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_pages'));

        // プラグインページのみに制限
        if (isset($_REQUEST["page"]) && $_REQUEST["page"] == ALPT_PLUGIN_NAME) {
            add_action('admin_notices', array($this, 'admin_notices'));
            add_action('admin_init', array($this, 'admin_init'));
        }
    }

    /**
     * 翻訳用
     */
    public function e($text)
    {
        _e($text, ALPT_TEXT_DOMAIN);
    }

    public function _($text)
    {
        return __($text, ALPT_TEXT_DOMAIN);
    }

    /**
     * 管理画面に設定ページを追加
     */
    public function add_pages()
    {
        add_options_page('Auto Page Template', 'Auto Page Templatet', 'manage_options', ALPT_PLUGIN_NAME, array($this, 'options_page'));
    }

    public function admin_init()
    {
        /**
         * テストテーマのOn/Offを設定
         */
        if (isset($_POST['_wpnonce']) && $_POST['_wpnonce']) {
            $errors  = new WP_Error();
            $updates = new WP_Error();

            if (check_admin_referer(ALPT_PLUGIN_NAME, '_wpnonce')) {

                //オプションを設定
                $options['can_push_post'] = esc_html($_POST['can_push_post']);

                update_option(ALPT_PLUGIN_NAME, $options);
                $updates->add('update', $this->_('Saved'));
                set_transient('wptt-updates', $updates->get_error_messages(), 1);
            } else {
                $errors->add('error', $this->_('An invalid value has been sent'));
                set_transient('wptt-errors', $errors->get_error_messages(), 1);
            }

        }
    }

    /**
     * アップデート表示
     */
    public function admin_notices()
    {
        ?>
        <?php if ($messages = get_transient('wptt-updates')): ?>
            <div class="updated">
                <ul>
                    <?php foreach ($messages as $key => $message): ?>
                        <li><?php echo esc_html($message); ?></li>
                    <?php endforeach;?>
                </ul>
            </div>
        <?php endif;?>

        <?php if ($messages = get_transient('wptt-errors')): ?>
            <div class="error">
                <ul>
                    <?php foreach ($messages as $key => $message): ?>
                        <li><?php echo esc_html($message); ?></li>
                    <?php endforeach;?>
                </ul>
            </div>
        <?php endif;?>
        <?php
    }

    /**
     * options_page
     */
    public function options_page()
    {
        // 保存されている情報を取得
        $options = get_option(ALPT_PLUGIN_NAME);
        // print_r($options);
        ?>
        <div class="plugin-wrap">
            <div class="plugin-main">
                <h1>Auto Load Page Template</h1>

                <form method="post" action="">
                    <?php wp_nonce_field(ALPT_PLUGIN_NAME, '_wpnonce');?>

                    <table class="form-table">
                        <tr>
                            <th><?php $this->e('Can Push Post')?></th>
                            <td> 
                                <input type="checkbox" name="can_push_post" value="1" <?php if(  $options['can_push_post']  ): ?>checked="checked"<?php endif; ?> >
                                <p>
                                    ex) $auto_load_page_template->push_post('.selector');
                                </p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit"><input type="submit" name="submit" value="<?php $this->e('Save')?>" class="button-primary" /></p>

                </form>
            </div><!-- /.plugin-main -->

        </div><!-- /.plugin-wrap -->
        <?php
    }
}
new ALPT_Admin();
