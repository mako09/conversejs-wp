<?php
/**
 * Plugin Name: converse.js for WordPress
 * Plugin URI: http://wordpress.org/plugins/conversejs-wp/
 * Description: Jabber/XMPP client Converse.js for WordPress
 * Version: 0.1
 * Author: Mako N
 * Author URI: http://pasero.net/~mako/
 * Text Domain: conversejs-wp
 * Domain Path: /languages
 * License: GPLv2 or later
 */
new Converse_Js();

class Converse_Js {

	public function __construct () {

		/* Internationalize the text strings used */
		add_action( 'plugins_loaded', array( &$this, 'i18n' ), 2 );

		/* header */
		add_action( 'wp_head', array( &$this, 'head' ) );

		/* footer */
		add_action( 'wp_footer', array( &$this, 'footer' ) );

		/* Settings */
		add_action( 'admin_init', array( &$this, 'settings_init' ) );
		add_action( 'admin_menu', array( &$this, 'options_add_page' ) );
	}

	/**
	 * Loads the translation files
	 *
	 * @since  0.1
	 * @return void
	 */
	public function i18n() {
		load_plugin_textdomain( 'conversejs', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Add lines in Header
	 *
	 * @since  0.1
	 * @return void
	 */
	public function head() {
		$css = plugin_dir_url( __FILE__ ) . "converse/converse.min.css";
?>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $css ?>">
<style type="text/css">
.icon-offline:before {
        content: "";
}
</style>
<?php
	}

	/**
	 * Add scripts near the end
	 *
	 * @since  0.1
	 * @return void
	 */
	public function footer() {
		$options = get_option('conversejs');
		if ( $options['conversejs_url'] == 'converse.min.js' || $options['conversejs_url'] == 'converse-no-otr.min.js' || $options['conversejs_url'] == 'converse-no-locales-no-otr.min.js') {
			$converse = plugin_dir_url( __FILE__ ) . 'converse/' . $options['conversejs_url'];
		} elseif ( filter_var( $options['conversejs_url'], FILTER_VALIDATE_URL ) ) {
			$converse = $options['conversejs_url'];
		} else {
			$converse = plugin_dir_url( __FILE__ ) . "converse/converse.min.js";
		}
?>
<script type="text/javascript" src="<?php echo includes_url() ?>js/utils.min.js"></script>
<script type="text/javascript" src="<?php echo $converse ?>"></script>
<script type="text/javascript">
	 var BOSH_SERVICE = '<?php echo ( filter_var( $options['bosh_server'], FILTER_VALIDATE_URL ) ) ? $options['bosh_server'] : 'https://bind.opkode.im'; ?>';
	 /*
					if ( wpCookies.get('jid') === null ) {
	conn = new Strophe.Connection(BOSH_SERVICE);
	conn.connect('anon.step.im', '', onConnect);
				}
	 */
function onConnect(status) {
	wpCookies.set('jid', conn.jid);
	wpCookies.set('sid', conn.sid);
	wpCookies.set('rid', conn.rid);
}

require(['converse'], function (converse) {
    converse.initialize({
        allow_contact_requests: <?php echo ($options['contact_requests']) ? 'true' : 'false'; ?>,
        auto_list_rooms: <?php echo ($options['list_rooms']) ? 'true' : 'false'; ?>,
        auto_subscribe: <?php echo ($options['auto_subscribe']) ? 'true' : 'false'; ?>,
		bosh_service_url: BOSH_SERVICE,
        allow_muc: <?php echo ($options['allow_muc']) ? 'true' : 'false'; ?>,
        animate: <?php echo ($options['animate']) ? 'true' : 'false'; ?>,
        hide_muc_server: <?php echo ($options['hide_muc_server']) ? 'true' : 'false'; ?>,
        i18n: locales.en, // Refer to ./locale/locales.js
<?php
			if ($options['prebind']) {
?>
        prebind: true,
        jid: wpCookies.get('jid'),
        rid: wpCookies.get('rid'),
        sid: wpCookies.get('sid'),
<?php
			}
?>
        show_controlbox_by_default: <?php echo ($options['show_controlbox']) ? 'true' : 'false'; ?>,
        show_only_online_users: <?php echo ($options['only_online_users']) ? 'true' : 'false'; ?>,
        use_vcards: <?php echo ($options['vcards']) ? 'true' : 'false'; ?>,
        xhr_user_search: <?php echo ($options['xhr_user_search']) ? 'true' : 'false'; ?>
    });
});
</script>
<div id="conversejs"></div>
<?php
	  }

/* ----- settings section -------- */

	public function settings_init () {
		register_setting( 'conversejs', 'conversejs' );
		add_settings_section('main_section', __( 'Main Settings', 'conversejs-wp' ), null, __FILE__ );
		add_settings_field('conversejs_url', __( 'Converse.js URL', 'conversejs-wp' ), array( &$this, 'conversejs_url' ), __FILE__, 'main_section' );
		add_settings_field('bosh_server', __( 'BOSH Server URL', 'conversejs-wp' ), array( &$this, 'bosh_server' ), __FILE__, 'main_section' );
		add_settings_field('prebind', __( 'Prebind', 'conversejs-wp' ), array( &$this, 'prebind' ), __FILE__, 'main_section' );
		add_settings_field('contact_requests', __( 'Allow Contact Requests', 'conversejs-wp' ), array( &$this, 'contact_requests' ), __FILE__, 'main_section' );
		add_settings_field('allow_muc', __( 'Allow MUC', 'conversejs-wp' ), array( &$this, 'allow_muc' ), __FILE__, 'main_section' );
		add_settings_field('animate', __( 'Animate', 'conversejs-wp' ), array( &$this, 'animate' ), __FILE__, 'main_section' );
		add_settings_field('list_rooms', __( 'Auto List Rooms', 'conversejs-wp' ), array( &$this, 'list_rooms' ), __FILE__, 'main_section' );
		add_settings_field('auto_subscribe', __( 'Auto Subscribe', 'conversejs-wp' ), array( &$this, 'auto_subscribe' ), __FILE__, 'main_section' );
		add_settings_field('hide_muc_server', __( 'Hide MUC Server', 'conversejs-wp' ), array( &$this, 'hide_muc_server' ), __FILE__, 'main_section' );
		add_settings_field('show_controlbox', __( 'Show controlbox', 'conversejs-wp' ), array( &$this, 'show_controlbox' ), __FILE__, 'main_section' );
		add_settings_field('only_online_users', __( 'Show Only Online Users', 'conversejs-wp' ), array( &$this, 'only_online_users' ), __FILE__, 'main_section' );
		add_settings_field('vcards', __( 'Use vCards', 'conversejs-wp' ), array( &$this, 'vcards' ), __FILE__, 'main_section' );
		add_settings_field('xhr_user_search', __( 'XHR User Search', 'conversejs-wp' ), array( &$this, 'xhr_user_search' ), __FILE__, 'main_section' );
	}

	public function options_add_page () {
		add_options_page( _x('Converse.js', 'menu', 'conversejs-wp'), _x('Converse.js', 'menu', 'conversejs-wp'), 'administrator', __FILE__, array( &$this, 'options_page' ) );
	}

	public function conversejs_url () {
		$options = get_option('conversejs');
		$url = filter_var( $options['conversejs_url'], FILTER_VALIDATE_URL );
?>

    <input id='conversejs_url' name='conversejs[conversejs_url]' size='40' type='text' title='<?php _e( 'From where Converse.js is loaded.', 'conversejs-wp' ) ?>' value='<?php echo $url ?>' />
<?php
	}

	public function contact_requests() {
		$options = get_option('conversejs');
		if($options['contact_requests']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='contact_requests' name='conversejs[contact_requests]' type='checkbox' /><label for='contact_requests' title='<?php _e('If this is set to false, the Add a contact widget, Contact Requests and Pending Contacts roster sections will all not appear. Additionally, all incoming contact requests will be ignored.', 'conversejs-wp') ?>'><?php _e('Allow users to add one another as contacts', 'conversejs-wp') ?></label>
<?php
	}

	public function allow_muc() {
		$options = get_option('conversejs');
		if($options['allow_muc']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='allow_muc' name='conversejs[allow_muc]' type='checkbox' /><label for='allow_muc' title='<?php _e('Setting this to false will remove the Chatrooms tab from the control box.', 'conversejs-wp') ?>'><?php _e('Allow multi-user chat (muc) in chatrooms', 'conversejs-wp') ?></label>
<?php
	}

	public function animate() {
		$options = get_option('conversejs');
		if($options['animate']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='animate' name='conversejs[animate]' type='checkbox' /><label for='animate' title='<?php _e('Show animations, for example when opening and closing chat boxes.', 'conversejs-wp') ?>'><?php _e('Show animations', 'conversejs-wp') ?></label>
<?php
	}

	public function list_rooms() {
		$options = get_option('conversejs');
		if($options['list_rooms']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='list_rooms' name='conversejs[list_rooms]' type='checkbox' /><label for='list_rooms' title='<?php _e('If true, and the XMPP server on which the current user is logged in supports multi-user chat, then a list of rooms on that server will be fetched.  Not recommended for servers with lots of chat rooms.', 'conversejs-wp') ?>'><?php _e('List rooms automatically', 'conversejs-wp') ?></label>
<?php
	}

	public function auto_subscribe() {
		$options = get_option('conversejs');
		if($options['auto_subscribe']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='auto_subscribe' name='conversejs[auto_subscribe]' type='checkbox' /><label for='auto_subscribe' title='<?php _e('If true, the user will automatically subscribe back to any contact requests.', 'conversejs-wp') ?>'><?php _e('Subscribe automatically', 'conversejs-wp') ?></label>
<?php
	}

	public function hide_muc_server() {
		$options = get_option('conversejs');
		if($options['hide_muc_server']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='hide_muc_server' name='conversejs[hide_muc_server]' type='checkbox' /><label for='hide_muc_server' title='<?php _e('Hide the server input field of the form inside the Room panel of the controlbox.  Useful if you want to restrict users to a specific XMPP server of your choosing.', 'conversejs-wp') ?>'><?php _e('Hide the server input field in the Room panel', 'conversejs-wp') ?></label>
<?php
	}

	public function show_controlbox() {
		$options = get_option('conversejs');
		if($options['show_controlbox']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='show_controlbox' name='conversejs[show_controlbox]' type='checkbox' /><label for='show_controlbox' title='<?php _e('By default, controlbox is hidden and can be toggled by clicking on any element in the page with class toggle-online-users.  If this options is set to true, the controlbox will by default be shown upon page load.', 'conversejs-wp') ?>'><?php _e('Show controlbox by default', 'conversejs-wp') ?></label>
<?php
	}

	public function only_online_users() {
		$options = get_option('conversejs');
		if($options['only_online_users']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='only_online_users' name='conversejs[only_online_users]' type='checkbox' /><label for='only_online_users' title='<?php _e('If set to true, only online users will be shown in the contacts roster.  Users with any other status (e.g. away, busy etc.) will not be shown.', 'conversejs-wp') ?>'><?php _e('Show only online users in roster', 'conversejs-wp') ?></label>
<?php
	}

	public function vcards() {
		$options = get_option('conversejs');
		if($options['vcards']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='vcards' name='conversejs[vcards]' type='checkbox' /><label for='vcards' title='<?php _e('Determines whether the XMPP server will be queried for roster contacts&#8217; VCards or not.  VCards contain extra personal information such as your fullname and avatar image.', 'conversejs-wp') ?>'><?php _e('Use vCards information', 'conversejs-wp') ?></label>
<?php
	}

	public function xhr_user_search() {
		$options = get_option('conversejs');
		if($options['xhr_user_search']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='xhr_user_search' name='conversejs[xhr_user_search]' type='checkbox' /><label for='xhr_user_search' title='<?php _e('There are two ways to add users.  (1)The user inputs a valid JID (Jabber ID), and the user is added as a pending contact.  (2)The user inputs some text (for example part of a firstname or lastname), an XHR (Ajax Request) will be made to a remote server, and a list of matches are returned.  The user can then choose one of the matches to add as a contact.  This setting enables the second mechanism, otherwise by default the first will be used.', 'conversejs-wp') ?>'><?php _e('Search user by AJAX', 'conversejs-wp') ?></label>
<?php
	}

	public function prebind() {
		$options = get_option('conversejs');
		if($options['prebind']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='prebind' name='conversejs[prebind]' type='checkbox' /><label for='prebind' title='<?php _e('This is useful when you don&#8217;t want to render the login form on the chat control box with each page load.', 'conversejs-wp') ?>'><?php _e('Prebind', 'conversejs-wp') ?></label>
<?php
	}

	public function bosh_server() {
		$options = get_option('conversejs');
		$url = filter_var( $options['bosh_server'], FILTER_VALIDATE_URL );
?>

    <input id='bosh_server' name='conversejs[bosh_server]' size='40' type='text' title='<?php _e( 'Connections to an XMPP server depend on a BOSH connection manager which acts as a middle man between HTTP and XMPP.', 'conversejs-wp' ) ?>' value='<?php echo $url ?>' />
<?php
	}

	public function options_page () {
?>
    <div class="wrap">
    <h2><?php _e('Converse.js Settings', 'conversejs-wp') ?></h2>
    <form method="post" action="options.php">
    <?php settings_fields( 'conversejs' ); ?>
    <?php do_settings_sections( __FILE__ ); ?>

    <p class="submit">
        <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
    </p>
    </form>
    </div>
<?php
	}
}
?>
