<?php
/**
 * Plugin Name: Converse.js for WordPress
 * Plugin URI: http://wordpress.org/plugins/conversejs-wp/
 * Description: Jabber/XMPP client Converse.js for WordPress.
 * Version: 0.7
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
		add_action( 'wp_footer', array( &$this, 'footer' ), 16 );

		/* Settings */
		register_activation_hook( __FILE__, 'set_defaults' );
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
		load_plugin_textdomain( 'conversejs-wp', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Add lines in Header
	 *
	 * @since  0.1
	 * @return void
	 */
	public function head() {
		$options = get_option('conversejs');
		$css = plugin_dir_url( __FILE__ ) . "converse/converse.min.css";
?>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $css ?>">
<?php
		if ( $options['prebind'] && ! $options['prebind_password'] ) { // for shared roster on anonymous server
?>
<style type="text/css">
.icon-offline:before {
        content: "";
}
</style>
<?php
		}
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

<?php
			if ($options['prebind']) {
?>
//if ( wpCookies.get('sid') == null ) {
conn = new Strophe.Connection(BOSH_SERVICE);
conn.connect('<?php echo $options['prebind_jid'] ?>', '<?php echo $options['prebind_password'] ?>', onConnect);
//}

function onConnect(status) {
	wpCookies.set('jid', conn.jid);
	wpCookies.set('sid', conn.sid);
	wpCookies.set('rid', conn.rid);
}
<?php
			}
?>
require(['converse'], function (converse) {
    converse.initialize({
        allow_contact_requests: <?php echo ($options['contact_requests']) ? 'true' : 'false'; ?>,
        auto_list_rooms: <?php echo ($options['list_rooms']) ? 'true' : 'false'; ?>,
        auto_subscribe: <?php echo ($options['auto_subscribe']) ? 'true' : 'false'; ?>,
		bosh_service_url: BOSH_SERVICE,
        allow_muc: <?php echo ($options['allow_muc']) ? 'true' : 'false'; ?>,
        animate: <?php echo ($options['animate']) ? 'true' : 'false'; ?>,
        hide_muc_server: <?php echo ($options['hide_muc_server']) ? 'true' : 'false'; ?>,
        i18n: <?php echo ($options['language']) ? 'locales.' . $options['language'] : 'locales.en'; ?>,
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

	public function set_defaults () {
		$options = get_option('conversjs');
		if ( !is_array( $options ) ) {
				$arr = array( "conversejs_url"    => "",
							  "bosh_server"       => "",
							  "contact_requests"  => "on",
							  "allow_muc"         => "on",
							  "language"          => "en",
							  "animate"           => "on",
							  "list_rooms"        => "",
							  "auto_subscribe"    => "",
							  "hide_muc_server"   => "",
							  "show_controlbox"   => "",
							  "only_online_users" => "",
							  "vcards"            => "on",
							  "xhr_user_search"   => "",
							  "prebind"           => "",
							  "prebind_jid"       => "",
							  "prebind_password"  => ""
							  );
		update_option('conversejs', $arr);
		}
	}

	public function settings_init () {
		register_setting( 'conversejs', 'conversejs' );
		add_settings_section( 'main_section', __( 'Main Settings', 'conversejs-wp' ), null, __FILE__ );
		add_settings_field( 'conversejs_url', __( 'Converse.js URL', 'conversejs-wp' ), array( &$this, 'conversejs_url' ), __FILE__, 'main_section' );
		add_settings_field( 'bosh_server', __( 'BOSH Server URL', 'conversejs-wp' ), array( &$this, 'bosh_server' ), __FILE__, 'main_section' );
		add_settings_field( 'contact_requests', __( 'Allow users to add one another as contacts', 'conversejs-wp' ), array( &$this, 'contact_requests' ), __FILE__, 'main_section' );
		add_settings_field( 'allow_muc', __( 'Allow multi-user chat (muc) in chatrooms', 'conversejs-wp' ), array( &$this, 'allow_muc' ), __FILE__, 'main_section' );
		add_settings_field( 'language', __( 'Language', 'conversejs-wp' ), array( &$this, 'language' ), __FILE__, 'main_section' );
		add_settings_field( 'animate', __( 'Show animations', 'conversejs-wp' ), array( &$this, 'animate' ), __FILE__, 'main_section' );
		add_settings_field( 'list_rooms', __( 'List Rooms automatically ', 'conversejs-wp' ), array( &$this, 'list_rooms' ), __FILE__, 'main_section' );
		add_settings_field( 'auto_subscribe', __( 'Subscribe automatically', 'conversejs-wp' ), array( &$this, 'auto_subscribe' ), __FILE__, 'main_section' );
		add_settings_field( 'hide_muc_server', __( 'Hide the server input field in the Room panel', 'conversejs-wp' ), array( &$this, 'hide_muc_server' ), __FILE__, 'main_section' );
		add_settings_field( 'show_controlbox', __( 'Show controlbox by default', 'conversejs-wp' ), array( &$this, 'show_controlbox' ), __FILE__, 'main_section' );
		add_settings_field( 'only_online_users', __( 'Show only online users in roster', 'conversejs-wp' ), array( &$this, 'only_online_users' ), __FILE__, 'main_section' );
		add_settings_field( 'vcards', __( 'Use vCards information', 'conversejs-wp' ), array( &$this, 'vcards' ), __FILE__, 'main_section' );
		add_settings_field( 'xhr_user_search', __( 'XHR User Search', 'conversejs-wp' ), array( &$this, 'xhr_user_search' ), __FILE__, 'main_section' );

		add_settings_section( 'prebind_section', __( 'Prebind Settings (experimental)', 'conversejs-wp' ), null, __FILE__ );
		add_settings_field( 'prebind', __( 'Prebind', 'conversejs-wp' ), array( &$this, 'prebind' ), __FILE__, 'prebind_section' );
		add_settings_field( 'prebind_jid', __( 'JID', 'conversejs-wp' ), array( &$this, 'prebind_jid' ), __FILE__, 'prebind_section' );
		add_settings_field( 'prebind_password', __( 'Password', 'conversejs-wp' ), array( &$this, 'prebind_password' ), __FILE__, 'prebind_section' );
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

    <input <?php echo( $checked ) ?> id='contact_requests' name='conversejs[contact_requests]' type='checkbox' title='<?php _e('If this is set to false, the Add a contact widget, Contact Requests and Pending Contacts roster sections will all not appear. Additionally, all incoming contact requests will be ignored.', 'conversejs-wp') ?>'/>
<?php
	}

	public function allow_muc() {
		$options = get_option('conversejs');
		if($options['allow_muc']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='allow_muc' name='conversejs[allow_muc]' type='checkbox' title='<?php _e('Setting this to false will remove the Chatrooms tab from the control box.', 'conversejs-wp') ?>'/>
<?php
	}

	function language() {
		$options = get_option('conversejs');
		$items = array( "af"    => __( 'Afrikaans', 'conversejs-wp' ),
						"de"    => __( 'German', 'conversejs-wp' ),
						"en"    => __( 'English', 'conversejs-wp' ),
						"es"    => __( 'Spanish', 'conversejs-wp' ),
						"fr"    => __( 'French', 'conversejs-wp' ),
						"hu"    => __( 'Hungarian', 'conversejs-wp' ),
						"it"    => __( 'Italian', 'conversejs-wp' ),
						"ja"    => __( 'Japanese', 'conversejs-wp' ),
						"nl"    => __( 'Dutch', 'conversejs-wp' ),
						"pt_BR" => __( 'Portuguese - BRAZIL', 'conversejs-wp' ),
						"ru"    => __( 'Russian', 'conversejs-wp' )
						);
		echo "<select id='language' name='conversejs[language]'>";
		reset ( $items );
		while ( list($key, $val) = each( $items ) ) {
			$selected = ( $options['language'] == $key ) ? 'selected="selected"' : '';
			echo "<option value='$key' $selected>$val</option>";
		}
		echo "</select>";
	}

	public function animate() {
		$options = get_option('conversejs');
		if($options['animate']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='animate' name='conversejs[animate]' type='checkbox' title='<?php _e('Show animations, for example when opening and closing chat boxes.', 'conversejs-wp') ?>'/>
<?php
	}

	public function list_rooms() {
		$options = get_option('conversejs');
		if($options['list_rooms']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='list_rooms' name='conversejs[list_rooms]' type='checkbox' title='<?php _e('If true, and the XMPP server on which the current user is logged in supports multi-user chat, then a list of rooms on that server will be fetched.  Not recommended for servers with lots of chat rooms.', 'conversejs-wp') ?>'/>
<?php
	}

	public function auto_subscribe() {
		$options = get_option('conversejs');
		if($options['auto_subscribe']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='auto_subscribe' name='conversejs[auto_subscribe]' type='checkbox' title='<?php _e('If true, the user will automatically subscribe back to any contact requests.', 'conversejs-wp') ?>'/ >
<?php
	}

	public function hide_muc_server() {
		$options = get_option('conversejs');
		if($options['hide_muc_server']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='hide_muc_server' name='conversejs[hide_muc_server]' type='checkbox' title='<?php _e('Hide the server input field of the form inside the Room panel of the controlbox.  Useful if you want to restrict users to a specific XMPP server of your choosing.', 'conversejs-wp') ?>'/>
<?php
	}

	public function show_controlbox() {
		$options = get_option('conversejs');
		if($options['show_controlbox']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='show_controlbox' name='conversejs[show_controlbox]' type='checkbox' title='<?php _e('By default, controlbox is hidden and can be toggled by clicking on any element in the page with class toggle-online-users.  If this options is set to true, the controlbox will by default be shown upon page load.', 'conversejs-wp') ?>'/>
<?php
	}

	public function only_online_users() {
		$options = get_option('conversejs');
		if($options['only_online_users']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='only_online_users' name='conversejs[only_online_users]' type='checkbox' title='<?php _e('If set to true, only online users will be shown in the contacts roster.  Users with any other status (e.g. away, busy etc.) will not be shown.', 'conversejs-wp') ?>'/>
<?php
	}

	public function vcards() {
		$options = get_option('conversejs');
		if($options['vcards']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='vcards' name='conversejs[vcards]' type='checkbox' title='<?php _e('Determines whether the XMPP server will be queried for roster contacts&#8217; VCards or not.  VCards contain extra personal information such as your fullname and avatar image.', 'conversejs-wp') ?>'/>
<?php
	}

	public function xhr_user_search() {
		$options = get_option('conversejs');
		if($options['xhr_user_search']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='xhr_user_search' name='conversejs[xhr_user_search]' type='checkbox' title='<?php _e('There are two ways to add users.  (1)The user inputs a valid JID (Jabber ID), and the user is added as a pending contact.  (2)The user inputs some text (for example part of a firstname or lastname), an XHR (Ajax Request) will be made to a remote server, and a list of matches are returned.  The user can then choose one of the matches to add as a contact.  This setting enables the second mechanism, otherwise by default the first will be used.', 'conversejs-wp') ?>'/>
<?php
	}

	public function prebind() {
		$options = get_option('conversejs');
		if($options['prebind']) { $checked = ' checked="checked" '; }
?>

    <input <?php echo( $checked ) ?> id='prebind' name='conversejs[prebind]' type='checkbox' title='<?php _e('This is useful when you don&#8217;t want to render the login form on the chat control box with each page load.', 'conversejs-wp') ?>'/>
<?php
	}

	public function bosh_server() {
		$options = get_option('conversejs');
		$url = filter_var( $options['bosh_server'], FILTER_VALIDATE_URL );
?>

    <input id='bosh_server' name='conversejs[bosh_server]' size='40' type='text' title='<?php _e( 'Connections to an XMPP server depend on a BOSH connection manager which acts as a middle man between HTTP and XMPP.', 'conversejs-wp' ) ?>' value='<?php echo $url ?>' />
<?php
	}

	public function prebind_jid() {
		$options = get_option('conversejs');
		$jid = filter_var( $options['prebind_jid'], FILTER_SANITIZE_EMAIL );
?>

    <input id='prebind_jid' name='conversejs[prebind_jid]' size='20' type='text' title='<?php _e( 'Jabber ID for prebinding.', 'conversejs-wp' ) ?>' value='<?php echo $jid ?>' />
<?php
	}

	public function prebind_password() {
		$options = get_option('conversejs');
		$password = $options['prebind_password'];
?>

    <input id='prebind_password' name='conversejs[prebind_password]' size='20' type='text' title='<?php _e( 'Password for prebinding.', 'conversejs-wp' ) ?>' value='<?php echo $password ?>' />
<?php
	}

	public function options_page () {
?>
    <div class="wrap">
    <h2><?php _e('Converse.js Settings', 'conversejs-wp') ?></h2>
    <form method="post" action="options.php">
    <?php settings_fields( 'conversejs' ); ?>
    <?php do_settings_sections( __FILE__ ); ?>

    <?php submit_button(); ?>
    </form>
    </div>
<?php
	}
}
?>
