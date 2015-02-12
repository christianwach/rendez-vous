<?php
/**
 * Rendez Vous Admin
 *
 * Admin class
 *
 * @package Rendez Vous
 * @subpackage Activity
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load Admin class.
 *
 * @package Rendez Vous
 * @subpackage Admin
 *
 * @since Rendez Vous (1.2.0)
 */
class Rendez_Vous_Admin {

	/**
	 * Setup Admin.
	 *
	 * @package Rendez Vous
 	 * @subpackage Admin
     *
     * @since Rendez Vous (1.2.0)
	 *
	 * @uses buddypress() to get BuddyPress main instance.
	 */
	public static function start() {
		$rdv = rendez_vous();

		if ( empty( $rdv->admin ) ) {
			$rdv->admin = new self;
		}

		return $rdv->admin;
	}

	/**
	 * The constructor
	 *
	 * @package Rendez Vous
 	 * @subpackage Admin
     *
     * @since Rendez Vous (1.2.0)
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_hooks();
	}

	/**
	 * Set some globals.
	 *
	 * @package Rendez Vous
 	 * @subpackage Admin
     *
     * @since Rendez Vous (1.2.0)
	 */
	private function setup_globals() {}

	/**
	 * Set the actions & filters
	 *
	 * @package Rendez Vous
 	 * @subpackage Admin
     *
     * @since Rendez Vous (1.2.0)
	 */
	private function setup_hooks() {
		// update plugin's db version
		add_action( 'bp_admin_init',            array( $this, 'maybe_update'   )      );

		// javascript
		add_action( 'bp_admin_enqueue_scripts', array( $this, 'enqueue_script' )      );

		// Page
		add_action( bp_core_admin_hook(),       array( $this, 'admin_menu'     )      );

		add_action( 'admin_head',               array( $this, 'admin_head'     ), 999 );

		add_action( 'bp_admin_tabs',            array( $this, 'admin_tab'      )      );
	}

	/**
	 * Update plugin version if needed
	 *
	 * @package Rendez Vous
 	 * @subpackage Admin
     *
     * @since Rendez Vous (1.2.0)
	 */
	public function maybe_update() {
		if ( version_compare( bp_get_option( 'rendez-vous-version', 0 ), rendez_vous()->version, '<' ) ) {
			//might be useful one of these days..
			bp_update_option( 'rendez-vous-version', rendez_vous()->version );
		}
	}

	/**
	 * Enqueue script
	 *
	 * @package BP Avatar Suggestions
	 * @subpackage Admin
	 * @since   1.1.0
	 *
	 * @todo  localize strings, enqueue some css rules
	 */
	public function enqueue_script() {
		$current_screen = get_current_screen();

		// Bail if we're not on the rendez-vous page
		if ( empty( $current_screen->id ) || strpos( $current_screen->id, 'rendez-vous' ) === false ) {
			return;
		}

		$suffix = SCRIPT_DEBUG ? '' : '.min';
		$rdv = rendez_vous();

		wp_enqueue_script( 'rendez-vous-admin-backbone', $rdv->plugin_js . "rendez-vous-admin-backbone$suffix.js", array( 'wp-backbone' ), $rdv->version, true );
	}

	/**
	 * Set the plugin's BuddyPress sub menu
	 *
	 * @package Rendez Vous
 	 * @subpackage Admin
     *
     * @since Rendez Vous (1.2.0)
	 */
	public function admin_menu() {
		$page  = bp_core_do_network_admin()  ? 'settings.php' : 'options-general.php';

		$hook = add_submenu_page(
			$page,
			__( 'Rendez-vous Settings', 'rendez-vous' ),
			__( 'Rendez-vous Settings', 'rendez-vous' ),
			'manage_options',
			'rendez-vous',
			array( $this, 'admin_display' )
		);

		add_action( "admin_head-$hook", array( $this, 'modify_highlight' ) );
	}

	/**
	 * Modify highlighted menu
	 *
	 * @package Rendez Vous
 	 * @subpackage Admin
     *
     * @since Rendez Vous (1.2.0)
	 */
	public function modify_highlight() {
		global $plugin_page, $submenu_file;

		// This tweaks the Settings subnav menu to show only one BuddyPress menu item
		if ( $plugin_page == 'rendez-vous') {
			$submenu_file = 'bp-components';
		}
	}

	/**
	 * Display the admin
	 *
	 * @package Rendez Vous
 	 * @subpackage Admin
     *
     * @since Rendez Vous (1.2.0)
     *
     * @todo  edit a term
	 */
	public function admin_display() {
		?>
		<div class="wrap">

			<h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs( esc_html__( 'Rendez-vous', 'rendez-vous' ) ); ?></h2>

			<h3><?php esc_html_e( 'Types', 'rendez-vous' ) ;?></h3>

			<p class="description"><?php esc_html_e( 'Add your type in the field below and hit the return key to save it.', 'rendez-vous' ) ;?>

			<div class="rendez-vous-terms-admin">
				<div class="rendez-vous-form"></div>
				<div class="rendez-vous-list-terms"></div>
			</div>

			<script id="tmpl-rendez-vous-term" type="text/html">
				<b>{{data.name}}</b> <a href="#" class="rdv-delete-item" data-term_id="{{data.id}}">x</a>
			</script>

		</div>
		<?php
	}

	/**
	 * Hide submenu
	 *
	 * @package Rendez Vous
 	 * @subpackage Admin
     *
     * @since Rendez Vous (1.2.0)
	 */
	public function admin_head() {
		$page  = bp_core_do_network_admin()  ? 'settings.php' : 'options-general.php';

		remove_submenu_page( $page, 'rendez-vous' );
	}

	/**
	 * Rendez-vous tab
	 *
	 * @package Rendez Vous
 	 * @subpackage Admin
     *
     * @since Rendez Vous (1.2.0)
	 */
	public function admin_tab() {
		$class = false;

		$current_screen = get_current_screen();

		// Set the active class
		if ( ! empty( $current_screen->id ) && strpos( $current_screen->id, 'rendez-vous' ) !== false ) {
			$class = "nav-tab-active";
		}
		?>
		<a href="<?php echo bp_get_admin_url( add_query_arg( array( 'page' => 'rendez-vous' ), 'admin.php' ) );?>" class="nav-tab <?php echo $class;?>" style="margin-left:-6px"><?php esc_html_e( 'Rendez-vous', 'rendez-vous' );?></a>
		<?php
	}
}

add_action( 'bp_init', array( 'Rendez_Vous_Admin', 'start' ), 14 );
