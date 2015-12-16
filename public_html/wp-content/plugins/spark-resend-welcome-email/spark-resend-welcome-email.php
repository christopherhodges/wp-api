<?php
/*
Plugin Name: Resend Welcome Email by Spark
Plugin URI: http://www.sparkexperience.com
Description: Resend a custom email message.
Version: 1.0.2
Author: SPARK Experience
Author URI: http://www.sparkexperience.com
*
*/



if ( !class_exists( 'SPARK_Resend_Welcome_Email' )) {

	class SPARK_Resend_Welcome_Email {

		/**
		*  initiates class
		*/

		public function __construct() {

			global $wpdb;

			if (!current_user_can('edit_user')) {
				return;
			}

			/* Define constants */
			self::define_constants();

			/* Define hooks and filters */
			self::load_hooks();

			/* adds admin listeners for processing actions */
			self::add_admin_listeners();


		}


		/**
		*  Defines constants
		*/
		public static function define_constants() {
			define('SPARK_Resend_Welcome_Email_CURRENT_VERSION', '1.0.2' );
			define('SPARK_Resend_Welcome_Email_LABEL' , 'Resend Welcome Email' );
			define('SPARK_Resend_Welcome_Email_SLUG' , plugin_basename( dirname(__FILE__) ) );
			define('SPARK_Resend_Welcome_Email_FILE' ,  __FILE__ );
			define('SPARK_Resend_Welcome_Email_URLPATH', plugins_url( ' ', __FILE__ ) );
			define('SPARK_Resend_Welcome_Email_PATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );
		}

		/**
		*  Loads hooks and filters selectively
		*/
		public static function load_hooks() {

			//add_filter( 'user_row_actions',  array( __CLASS__ , 'filter_user_row_actions' ), 10, 2 );
			add_filter( 'personal_options',  array( __CLASS__ , 'personal_options' ), 10, 2 );

		}


		/**
		 *  Discovers which tests to run and runs them
		 */
		public static function filter_user_row_actions(  array $actions, WP_User $user ) {

			if ( ! $link = self::send_welcome_email_url( $user ) ) {
				return $actions;
			}

			$actions['send_welcome_email'] = '<a href="' . $link . '">' . __( 'Resend Welcome Email', 'resend-welcome-email' ) . '</a>';

			return $actions;
		}

		public static function personal_options(  WP_User $user ) {
			if ( ! $link = self::send_welcome_email_url( $user ) ) {
				return $actions;
			}

			?>
			<tr>
				<th scope="row"><?php _e( 'Send Welcome Email',  'user-switching' ); ?></th>
				<td><a href="<?php echo $link; ?>"><?php _e( 'Send Email', 'user-switching' ); ?></a></td>
			</tr>
			<?php
		}

		/**
		 *  Listens for email send commands and fires them
		 */
		public static function add_admin_listeners() {
			if (!isset($_GET['action']) || $_GET['action'] != 'SPARK_Resend_Welcome_Email' ) {
				//wp_die();
				return;
			}

			/* Resend welcome email */
			self::SPARK_Resend_Welcome_Email();

			/* Register success notice */
			add_action( 'admin_notices', array( __CLASS__ , 'define_notice') );


		}

		/**
		 *  Register admin notice that email has been sent
		 */
		public static function define_notice() {
			?>
			<div class="updated">
				<p><?php _e( 'Welcome email sent!' , 'resend-welcome-email'); ?></p>
			</div>
			<?php
		}

		/**
		 * Helper function. Returns the switch to or switch back URL for a given user.
		 *
		 * @param  WP_User $user The user to be switched to.
		 * @return string|bool The required URL, or false if there's no old user or the user doesn't have the required capability.
		 */
		public static function send_welcome_email_url( WP_User $user ) {

			return wp_nonce_url( add_query_arg( array(
				'action'  => 'SPARK_Resend_Welcome_Email',
				'user_id' => $user->ID
			), '') , "send_welcome_email_{$user->ID}" );

		}

		/**
		 * Resends the welcome email
		 *
		 * @param  int  $user_id      The ID of the user to re-send welcome email to
		 * @return bool|WP_User WP_User object on success, false on failure.
		 */
		public static function SPARK_Resend_Welcome_Email( ) {

			global $wpdb;

			$user_id = $_GET['user_id'];

			if ( !$user = get_userdata( $user_id ) ) {
				return false;
			}


			$user = get_userdata($user_id);


			$to = $user->data->user_email;
			$subject = get_option('spark_email_subject');
			$message = get_option('spark_email_message');

			// Replace strings


			$blogname = esc_html(get_option('blogname'), ENT_QUOTES);
			$site_url = site_url();
			$user_login = stripslashes($user->user_login);
			$first_name = $user->first_name;
			$last_name = $user->last_name;

			// Generate a key.
			$key = wp_generate_password( 20, false );

			do_action( 'retrieve_password_key', $user->user_login, $key );

			// Now insert the key, hashed, into the DB.
			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . WPINC . '/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}

			$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

			$login_url = $reset_url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');


			// [site_url]
			// [user_login]
			// [login_url]
			// [reset_url]
			// [blog_name]
			// [first_name]
			// [last_name]

			$message = str_replace('[site_url]', $site_url, $message);
			$message = str_replace('[user_login]', $user_login, $message);
			$message = str_replace('[login_url]', $site_url . '/wp-login.php', $message);
			$message = str_replace('[reset_url]', $reset_url, $message);
			$message = str_replace('[blog_name]', $blogname, $message);
			$message = str_replace('[first_name]', $first_name, $message);
			$message = str_replace('[last_name]', $last_name, $message);


			wp_mail( $to, $subject, $message );


		}


	}


	/**
	*  Load SPARK_Resend_Welcome_Email class in init
	*/
	function Load_SPARK_Resend_Welcome_Email() {
		$SPARK_Resend_Welcome_Email = new SPARK_Resend_Welcome_Email();
	}
	add_action( 'admin_init' , 'Load_SPARK_Resend_Welcome_Email' , 99 );


	// Main Menu
	function spark_email_menus() {
		add_options_page( 'Settings', 'Resend Welcome Email', 'manage_options', 'resend-welcome-email', 'spark_email_menus_settings' );
		add_action( 'admin_init','spark_email_menus_register_settings' );
	}

	function spark_email_menus_settings() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// Include the settings page
		include ('spark-email-settings.php');

	}

	// Register the available settings
	function spark_email_menus_register_settings() {
		register_setting( 'spark_email_general-settings', 'spark_email_address' );
		register_setting( 'spark_email_general-settings', 'spark_email_name' );
		register_setting( 'spark_email_general-settings', 'spark_email_subject' );
		register_setting( 'spark_email_general-settings', 'spark_email_message' );
	}



	add_action( 'admin_menu', 'spark_email_menus' );

	add_filter( 'wp_mail_content_type', 'set_html_content_type' );
	function set_html_content_type() {
		return 'text/html';
	}


}
