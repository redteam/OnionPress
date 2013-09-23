<?php
/**
 * @package OnionPress
 * @version 0.1.0
 */
/*
Plugin Name: OnionPress
Plugin URI: http://wordpress.org/extend/plugins/OnionPress/
Description: Bring privacy and online anonymity to your WordPress site. Tor Hidden Services for WordPress.<br><a href="https://www.torproject.org/"> Tor</a> is free software and an open network that helps you defend against a form of network surveillance that threatens personal freedom and privacy, confidential business activities and relationships, and state security known as traffic analysis... <a href="https://www.torproject.org/"> Learn more about Tor here </a>
Version:  0.1.1
Author: SiNA Rabbani
Author URI: https://onionpress.redteam.io
License: GPL2
*/


/**
* Short description for class
*
* Long description for class (if any)...
*
* @category   PrivacyPack
* @package    OnionPress
* @license    http://framework.zend.com/license   BSD License
* @version    Release: 0.0.1
* @link       http://code.redeam.io/oninpress
* @since      Class available since Release 1.0.0
*/

class OnionPress {   

    /** Documentation Block Here */
    public function __construct() {
        
        if ( !current_user_can( 'manage_options' ) )
            return;

       	if(trim($_SERVER['SERVER_NAME']) == $this->hs_hostname()){
            echo $_SERVER['SERVER_NAME'];
        		// Call 'load_onion_definitions' during WP initialization
            	add_action('wp_loaded',array($this,'load_onion_definitions'));
          		add_action('init',array($this,'load_onion_definitions'));
        }
        
        // Hoook into the 'wp_dashboard_setup' action to register our other functions
        add_action('wp_dashboard_setup', array($this,'OnionPress_add_dashboard_widgets' ));
        add_action('admin_menu', array($this,'OnionPress_menu_pages'));
    }

    /** Documentation Block Here */
    function is_active(){
        return true;
    }


    /** Documentation Block Here */
    function tor_controller() {
        # is Tor connected?
        # is wordpress onion server reachable?
        # what socks port?
        # 
    }
    /**
     * Load the necessary WordPress definitions.
     * Here we enable SOCKS Proxy, force the .onion hostname to load in our html content,
     * and block all remote connections, except for destinations: *.wordpress.org 
     * we are using MapAddress in our torrc configuration to Map *.wordpress.org to a hidden service
     * which is supposed to be operated by WordPress.org.
     */
    function load_onion_definitions(){
            $url_parsed = parse_url(site_url());
            print_r($url_parsed);
              
            define('WP_HOME',$url_parsed['scheme']."://".trim($this->hs_hostname()).trim($url_parsed['path']));
            define('WP_SITEURL',$url_parsed['scheme']."://".trim($this->hs_hostname()).trim($url_parsed['path']));
            define('WP_HTTP_BLOCK_EXTERNAL', true);  // block external requests
            define('WP_ACCESSIBLE_HOSTS', '*.wordpress.org'); // whitelist hosts
            define('WP_PROXY_HOST', '127.0.0.1');
            define('WP_PROXY_PORT', '9050');
            define('WP_PROXY_TYPE', 'CURLPROXY_SOCKS5');
            echo  bloginfo( 'stylesheet_url');
			// Enable Debug logging to the /wp-content/debug.log file
			define('WP_DEBUG_LOG', true);
    }

    /** Documentation Block Here */
    public function OnionPress_Folder(){
        $options_array = array();
        $options_array = unserialize(get_option('OnionPress_options'));
        // If OnionFolder is not defined, create it. 
        // Return OnionFolder
        if (is_array($options_array)){
            if (!$options_array['OnionFolder']){
                # TODO
                # check me!! Am I easy to bruteforce/guess?
                $onioin_folder = hash_hmac("sha256", rand(), rand());
                $options_array['OnionFolder'] = $onioin_folder;
                $variable = serialize($options_array);
                update_option('OnionPress_options', $variable);                
            }
        }
        return WP_PLUGIN_DIR . "/OnionPress/". $options_array['OnionFolder'];
    }

    /** Documentation Block Here */
    public function load_tor_config(){
        $socket = $this->get_socket();
        # FIXME
        echo $config_line = $this->OnionPress_Folder()."/App/./tor-linux-64 -f  ". $this->OnionPress_Folder() . "/Data/Tor/torrc --PidFile ".$this->OnionPress_Folder() . "/Data/Tor/tor.pid "." --DataDirectory " . 
        $this->OnionPress_Folder() . "/Data/Tor --HiddenServiceDir " . $this->OnionPress_Folder() . "/Data/Tor/hs --HiddenServicePort \"". " 80 ". $socket[1].":".$socket[0]."\"";
        return $config_line;
    }


    /** Documentation Block Here */
    # check if Tor is already running   
    public function get_pid(){
        if (file_exists($this->OnionPress_Folder() . "/Data/Tor/tor.pid")){
            $pid = file_get_contents($this->OnionPress_Folder() . "/Data/Tor/tor.pid");
            return $pid;
        }else{
            return FALSE;
            }
    }

    /** Documentation Block Here */    
    public function stop_tor() {
        $pid = $this->get_pid();
        if (posix_kill($pid, 9)){
            unlink($this->OnionPress_Folder() . "/Data/Tor/tor.pid");
        }
    }        


    /** Documentation Block Here */
    // Create the function use in the action hook
    public function OnionPress_add_dashboard_widgets() {
        wp_add_dashboard_widget('OnionPress_dashboard_widget', 'OnionPress Settings', array($this,'OnionPress_settings_dashboard'));
    }

    /** Documentation Block Here */
    public function is_tor_running() {
    	$pid = $this->get_pid();
        $dir = "/proc/".trim($pid);

        if($pid && is_dir($dir)) {
            return "YES";
        }else{
           if ($pid && !is_dir($dir)){
               unlink($this->OnionPress_Folder() . "/Data/Tor/tor.pid");
           }
           return "NO";
        }
    }

    /** Documentation Block Here */
    # run the Tor application
    public function run_tor(){
            $tor_command = "export LD_LIBRARY_PATH=\"" . $this->OnionPress_Folder() . "/Lib/\" && " . $this->load_tor_config();
            try{
                    return $this->terminal($tor_command);
            }catch (Exception $e){
                throw new Exception( 'Something went wrong, really wrong.', 0, $e);
            }
    }     

    /** Documentation Block Here */
    public function hs_hostname(){
        try{
            return file_get_contents($this->OnionPress_Folder() . "/Data/Tor/hs/hostname");
        }catch (Exception $e){
            throw new Exception( 'Hidden Service hostname file is not available.', 0, $e);
        }
    }

    /** Documentation Block Here */
    public function start_up_check(){

    	$error = '';

    	/** We only support linux at the moment if not linux exit */


    	/** Get CPU info: 32 or 64 bit? */

		$data = file('/proc/cpuinfo');
		foreach( $data as $line ) {
			if( preg_match('/^clflush/', $line) && preg_match('/64$/', $line) ) {
				$cpuarc = 64;
				break;
			}elseif (preg_match('/^clflush/', $line) && preg_match('/32$/', $line)) {
                $cpuarc = 64;
                break;
            }
		}

		if ($cpuarc != 64 && $cpuarc != 32){
			echo "Unlable to determine cpu architecture.";
			$error = true;
		}

		switch ($cpuarc) {
			case '64':
				# server is 64 bit linux
				# is binary available
				# 

				break;
			
			default:
				# code...
				break;
		}

        // is OnionPress created?
        // make one if not.
        if (!is_dir($this->OnionPress_Folder()) && is_dir(WP_PLUGIN_DIR . "/OnionPress/content")){
          try{
                rename(WP_PLUGIN_DIR . "/OnionPress/content" , $this->OnionPress_Folder());
            }catch (Exception $e){
                throw new Exception( 'failed to renmae Onion Folder', 0, $e);
                echo $e;
            }
        }

        if (!$error) {
        	# code...
        	return true;
        }else{
        	return FALSE;
        }
    }

    /** Documentation Block Here */
    public function terminal($command) {
    	//system
    	if(function_exists('system'))
    	{
    		ob_start();
    		system($command , $return_var);
    		$output = ob_get_contents();
    		ob_end_clean();
    	}
    	//passthru
    	else if(function_exists('passthru'))
    	{
    		ob_start();
    		passthru($command , $return_var);
    		$output = ob_get_contents();
    		ob_end_clean();
    	}
    	
    	//exec
    	else if(function_exists('exec'))
    	{
    		exec($command , $output , $return_var);
    		$output = implode("\n" , $output);
    	}
    	
    	//shell_exec
    	else if(function_exists('shell_exec'))
    	{
    		$output = shell_exec($command) ;
    	}
    	
    	else
    	{
    		$output = 'Command execution not possible on this system';
    		$return_var = 1;
    	}
	
	   return array('output' => $output , 'status' => $return_var);
    }
    
    /** Documentation Block Here */
    // get the IP and PORT of the web-server
    public function get_socket(){
            if(filter_var($_SERVER['SERVER_ADDR'], FILTER_VALIDATE_IP)) {
                $socket_ip = $_SERVER['SERVER_ADDR'];
            }
            if(filter_var($_SERVER['SERVER_PORT'], FILTER_VALIDATE_INT)) {
                $socket_port = $_SERVER['SERVER_PORT'];
            }
            
            return array($socket_port,$socket_ip);
    }
    

    /** Documentation Block Here */
    public function OnionPress_menu_pages() {
        // Add the top-level admin menu
        $page_title = 'OnionPress Settings';
        $menu_title = 'OnionPress';
        $capability = 'manage_options';
        $menu_slug = 'OnionPress-settings';
        $function = array($this,'OnionPress_settings');
        add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function); 

        // Add submenu page with same slug as parent to ensure no duplicates
        $sub_menu_title = 'Settings';
        add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function);

        // Now add the submenu page for Help
        $submenu_page_title = 'OnionPress Help';
        $submenu_title = 'Help';
        $submenu_slug = 'OnionPress-help';
        $submenu_function = 'OnionPress_help';
        add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
    }


    /** Documentation Block Here */
    public function OnionPress_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }
        // Render the HTML for the Settings page or include a file that does
        $this->OnionPress_settings_dashboard();
    }

    /** Documentation Block Here */
    public function OnionPress_help() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        // Render the HTML for the Help page or include a file that does
    }

    /** Documentation Block Here */
    // Create the function to output the contents of our Dashboard Widget
    public function OnionPress_settings_dashboard() {
        // create custom plugin settings menu
        // Display whatever it is you want to show
        echo '<div id="docs-left" style="background-color: blue; color: white; padding: 4px; ">';
       
        if ($this->is_tor_running() == "YES"){
            echo "Tor running as PID:". $this->get_pid();
            echo "<a style=\"\" href=\"admin.php?page=OnionPress-settings\">Settings</a><br>";
            echo "<h1>http://".$this->hs_hostname()."</h1>";
            echo site_url();
        }

        if ($this->is_tor_running() == "NO"){
            echo "Running Tor...<pre>";
            print_r($this->run_tor());
            echo "</pre>";
        }
        echo '</div><div style="clear:both"></div>';
    }


    /** Documentation Block Here */
    public function init() {
		static $instance = false;

		if ( !$instance ) {
			$instance = new OnionPress();
		}
        return $instance;
	}

}

OnionPress::init();