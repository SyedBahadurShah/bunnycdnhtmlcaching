<?php
	function d($data) {
		echo "<pre>";
		print_r($data);
		exit();		
	}

	function reverse_scheme() {

		$scheme = $_SERVER['REQUEST_SCHEME'];

		if ( strpos( $scheme, 's' ) !== false ) {
			return "http://";
		} else {
			return "https://";
		}
	}

	function bunnycdnhtmlcaching_get_host($type) {

		$siteurl = $_SERVER['HTTP_HOST'];
		$non_www = $siteurl;
		$www = "www." . $siteurl;

		if (strpos($siteurl, "www.") == true) {
			$www = $siteurl;
			$non_www = str_replace("www.", "", $siteurl);
		}

		if ($type == "www") {
			return $www;
		}

		return $non_www;	
	}	

	function bunnycdnhtmlcaching_get_zone_name() {
		$siteurl = $_SERVER['HTTP_HOST'];
		$zone_name = str_replace("_", "-", $siteurl);
		$zone_name = str_replace(".", "-", $zone_name);
		return $zone_name;
	}

	function bunnycdnhtmlcaching_get_settings() {

		$settings = get_option("bunnycdnhtmlcaching_settings");
		if (is_serialized($settings)) {
			$settings = unserialize($settings);
		}

		return $settings;	
	}

	function bunnycdnhtmlcaching_does_host_exist($zones) {

		$www_host_exist = false;
		$non_www_host_exist = false;

		$www_host = "www." . $_SERVER['HTTP_HOST'];
		$non_www_host = $_SERVER['HTTP_HOST'];

		if (strpos($_SERVER['HTTP_HOST'], "www.") == true) {
			$www_host = $_SERVER['HTTP_HOST'];
			$non_www_host = str_replace("www.", "", $_SERVER['HTTP_HOST']);
		}

		foreach ($zones as $zone) {

			foreach ($zone['host_names'] as $host) {

				if ($host == $non_www_host) {
					$non_www_host_exist = true;
				}

				if ($host == $www_host) {
					$www_host_exist = true;
				}	

			}

			if ($non_www_host_exist || $www_host_exist) {
				
				$message = "Hostname " . $non_www_host . " or " . $www_host . " already exist in " . $zone['zone_name'] . ".b-cdn.net Pullzone. Please delete hostname from it and try again.";
				if (is_admin()) {
					add_settings_error( 'bunnycdnhtmlcaching_settings', 200, $message, "error" );
				}		

				return true;
			}

		}

		return false;
	}

	function bunnycdnhtmlcaching_is_valid_api_key($api_key) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://bunnycdn.com/api/pullzone");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  "AccessKey: $api_key",
		  "Content-Type: application/json",
		  "Accept: application/json"
		));

		$response = curl_exec($ch);
		curl_close($ch);

		$response = json_decode($response);
		if (isset($response->Message) && $response->Message = "Authorization has been denied for this request.") {
			return false;
		}	

		return true;
	}

	function bunnycdnhtmlcaching_use_https_instead_of_http_notice() {
		
		$site_url = get_site_url();
		$home_url = get_home_url();

		if ( strpos( $_SERVER['SERVER_PORT'], '443' ) == true && strpos( $site_url, 'http://' || strpos($home_url, 'http://') ) ) {
			$message = __('Kindly use HTTPS scheme instead HTTP in WordPress Address (URL) and Site Address (URL) fields in your <a rel="noopener" href="/wp-admin/options-general.php">General Settings</a>', "bunnycdnhtmlcaching");
			if (is_admin()) {
				add_settings_error( 'bunnycdnhtmlcaching_settings', 200, $message, "error" );  
			}
		}
	}

	add_action( 'admin_notices', 'bunnycdnhtmlcaching_use_https_instead_of_http_notice' );	

	function bunnycdnhtmlcaching_get_site_version() {
		$siteurl = $_SERVER['HTTP_HOST'];
		if (substr($siteurl, 0, 4) == "www.") {
			return "www";
		}
		return "non-www";
	}

	function bunnycdnhtmlcaching_is_plugin_setting_page() {
		if ( isset($_GET['page']) && $_GET['page'] == "bunnycdnhtmlcaching" ) {
			return true;
		}
		return false;
	}

	function bunnycdnhtmlcaching_get_bunnycdn_pullzone_id() {
		$settings = get_option("bunnycdnhtmlcaching_settings");
		if (is_serialized($settings)) {
			$settings = unserialize($settings);
		}
		
		$bunnycdn_pullzone_id = isset($settings['bunnycdn_pullzone_id']) ? $settings['bunnycdn_pullzone_id'] : false;
		if ($bunnycdn_pullzone_id) {
			return $bunnycdn_pullzone_id;
		}
		return 0;
	}

	function bunnycdnhtmlcaching_get_bunnycdn_ssl_certificate() {

		$bunnycdn_has_ssl_certificate = false;
		$settings = get_option("bunnycdnhtmlcaching_settings");

		if (is_serialized($settings)) {
			$settings = unserialize($settings);
		}

		$bunnycdn_api_key = isset($settings['bunnycdn_api_key']) ? $settings['bunnycdn_api_key'] : false;
		$bunnycdn_pullzone_id = isset($settings['bunnycdn_pullzone_id']) ? $settings['bunnycdn_pullzone_id'] : false;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://bunnycdn.com/api/pullzone/" . $bunnycdn_pullzone_id);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"AccessKey: $bunnycdn_api_key",
			"Content-Type: application/json",
			"Accept: application/json"
		));

		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response, true);
		$certificates = 0;

		if (isset($response['Hostnames'])) {
			foreach ($response['Hostnames'] as $host) {
				if ($host['HasCertificate']) {
					$certificates++;
				}			
			}
			
			if ((int) $certificates == 3) {
				$bunnycdn_has_ssl_certificate = true;
			}
		}

		
		return $bunnycdn_has_ssl_certificate;

	}


	function bunnycdnhtmlcaching_non_www_to_www_notice() {	
		if (is_admin() && bunnycdnhtmlcaching_is_plugin_setting_page('bunnycdnhtmlcaching')) {
			if (strpos($_SERVER['HTTP_HOST'], 'www') === false && substr_count($_SERVER['HTTP_HOST'], ".") == 1) {
				$message = __("For better performance, consider switching from non-www to www <a id='site_version_switching' href='#'>Switch now</a>", "bunnycdnhtmlcaching");
				if (is_admin()) {
					add_settings_error( 'bunnycdnhtmlcaching_settings', 200, $message, "info" );  
				}
			}
		}	
	}		

	add_action( 'admin_notices', 'bunnycdnhtmlcaching_non_www_to_www_notice' );	


	function bunnycdnhtmlcaching_invalid_api_key_notice() {
	    if (bunnycdnhtmlcaching_is_plugin_setting_page()) {
			$settings = get_option('bunnycdnhtmlcaching_settings');
			if (is_serialized($settings)) {
				$settings = unserialize($settings);
			}

			if (isset($settings['bunnycdn_api_key']) && !empty($settings['bunnycdn_api_key']) && !bunnycdnhtmlcaching_is_valid_api_key($settings['bunnycdn_api_key']) ) {
			    add_settings_error( 'bunnycdnhtmlcaching_settings', 200, __('Invalid or Expired BunnyCDN Key!', 'bunnycdnhtmlcaching'), "error" );  
			}
		}
	}

	add_action( 'admin_notices', 'bunnycdnhtmlcaching_invalid_api_key_notice' );

	function bunnycdnhtmlcaching_flexible_ssl() {
		if (isset($_SERVER['HTTP_CF_VISITOR']) && isset($_SERVER['SERVER_PORT'])) {
			$visitor = json_decode(str_replace("\\", "", $_SERVER['HTTP_CF_VISITOR']));
			if ( $_SERVER['SERVER_PORT'] == 80 && $visitor->scheme == 'https') {
				$message = __('Flexible SSL Found!', 'bunnycdnhtmlcaching');
				if (is_admin()) {
					add_settings_error( 'bunnycdnhtmlcaching_settings', 200, $message, "error" );    	
				}		
			}
		}		
	}
	
	add_action( "admin_notices", "bunnycdnhtmlcaching_flexible_ssl" );

	add_action( 'updated_option', function( $option_name, $old_value, $value ) {

		if ($option_name == "bunnycdnhtmlcaching_settings") {
		    $settings = get_option("bunnycdnhtmlcaching_settings");
		    $bunnycdn_api_key = isset($settings['bunnycdn_api_key']) ? $settings['bunnycdn_api_key'] : false;
		    $site_version = isset($settings['site_version']) ? $settings['site_version'] : "";
		    
		    if ($site_version == 'www') {
				if (is_admin()) {
					$site_url = get_option("siteurl");
					$parse_url = parse_url($site_url);
					$www_site_version = $site_url;

					if (strpos($_SERVER['HTTP_HOST'], 'www.') === false) {
						$path = isset($parse_url['path']) ? $parse_url['path'] : '';
						$www_site_version = $_SERVER['REQUEST_SCHEME'] . "://www." . $_SERVER['HTTP_HOST'] . $path;
						update_option("siteurl", $www_site_version);
						update_option("home", $www_site_version);
						header("Location: " . $www_site_version . "/wp-admin");	
						exit();			
					}
				}	
		    }



		    if (bunnycdnhtmlcaching_is_valid_api_key($bunnycdn_api_key)) {
				if (bunnycdnhtmlcaching_run_bunnycdn_setup()) {
					$message = __('BunnyCDN Pullzone is successfully configured.', 'bunnycdnhtmlcaching');
					if (is_admin()) {
						add_settings_error( 'bunnycdnhtmlcaching_settings', 200, $message, "success" );    	
					}	
					return true;
				}
				return false;
		    }
		}

	}, 10, 3);	

	add_action( 'added_option', function( $option_name, $option_value ) {

		if ($option_name == "bunnycdnhtmlcaching_settings") {        
		    $settings = get_option("bunnycdnhtmlcaching_settings");
		    $bunnycdn_api_key = $settings['bunnycdn_api_key'];
		    $site_version = $settings['site_version'];

		    if ($site_version == 'www') {
				if (is_admin()) {
					$site_url = get_option("siteurl");
					$parse_url = parse_url($site_url);
					$www_site_version = $site_url;

					if (strpos($_SERVER['HTTP_HOST'], 'www.') === false) {
						$path = isset($parse_url['path']) ? $parse_url['path'] : '';
						$www_site_version = $_SERVER['REQUEST_SCHEME'] . "://www." . $_SERVER['HTTP_HOST'] . $path;
						update_option("siteurl", $www_site_version);
						update_option("home", $www_site_version);

						header("Location: " . $www_site_version . "/wp-admin");	
						exit();
					}	
				}	
		    }

		    if (bunnycdnhtmlcaching_is_valid_api_key($bunnycdn_api_key)) {
				if (bunnycdnhtmlcaching_run_bunnycdn_setup()) {
					$message = __('BunnyCDN Pullzone is successfully configured.', 'bunnycdnhtmlcaching');
					if (is_admin()) {
						add_settings_error( 'bunnycdnhtmlcaching_settings', 200, $message, "success" );    	
					}	
				}
		    }
		}
	}, 10, 3);	


	add_action( 'save_post', 'bunnycdnhtmlcaching_purge_on_post', 10, 3 );
	function bunnycdnhtmlcaching_purge_on_post( $post_id, $post, $update ) {
		if ($update && $post->post_status != "auto-draft") { // Update: Remove '&& $post->post_status != "draft" '
			$settings = get_option("bunnycdnhtmlcaching_settings");
			$siteurl = get_option("siteurl");

			$feedurl = $siteurl . "/feed/";
			$commentfeedurl = $siteurl . "/comments/feed/";	
			$blog_id = get_option('page_for_posts');
			$author_url = get_author_posts_url( $post_id );

			if ($blog_id > 0) {
				$siteurl = get_the_permalink($blog_id);
			}

			if (is_serialized($settings)) {
				$settings = unserialize($settings);
			}

			$amp_url = false;
			if ( function_exists( 'is_amp_endpoint' )) {
				$amp_url = amp_get_permalink( $post_id );
			}
			
			$account_key = $settings['bunnycdn_api_key'];
			$cdn = new bunnycdn_api();

			$cdn->Account( $account_key )->PurgeCache( $siteurl );		
			$cdn->Account( $account_key )->PurgeCache( get_permalink( $post_id ) );

			if ($amp_url) {
				$cdn->Account( $account_key )->PurgeCache( $amp_url );
			}
			
			$category_base = "/category/";
			if (get_option( 'category_base' )) {
				$category_base = "/" . get_option( 'category_base' ) . "/";
			}

			$tag_base = "/tag/";
			if (get_option( 'tag_base' )) {
				$tag_base = "/" . get_option( 'tag_base' ) . "/";
			}

			foreach (get_the_category($post_id) as $category) {
				$category_url = $siteurl . $category_base . $category->slug . "/";
				$cdn->Account( $account_key )->PurgeCache( $category_url );
			}

			foreach (get_the_tags($post_id) as $tag) {
				$tag_url = $siteurl . $tag_base . $tag->slug . "/";
				$cdn->Account( $account_key )->PurgeCache( $tag_url );
			}
			
			$cdn->Account( $account_key )->PurgeCache( $feedurl );
			$cdn->Account( $account_key )->PurgeCache( $commentfeedurl );
			
			$cdn->Account( $account_key )->PurgeCache( $author_url );
			$cdn->Account( $account_key )->PurgeCache( get_permalink( $post_id . "feed" ) );
			$cdn->Account( $account_key )->PurgeCache( get_permalink( $post_id . "comments/feed/" ) );	
			

		}
	}
	
	add_action('comment_post', 'bunnycdnhtmlcaching_purge_on_comment');
	function bunnycdnhtmlcaching_purge_on_comment($comment_id) {
		
		$comment = get_comments()[0];
		$post_url = get_permalink( $comment->comment_post_ID );

		$settings = bunnycdnhtmlcaching_get_settings();
		$account_key = $settings['bunnycdn_api_key'];
		$cdn = new bunnycdn_api();

		if ($account_key) {
			$cdn->Account( $account_key )->PurgeCache( $post_url );
		}
	}		
	
	add_action( 'transition_comment_status', 'bunnycdnhtmlcaching_purge_on_comment_status_update', 10, 3 );
	function bunnycdnhtmlcaching_purge_on_comment_status_update($new_status, $old_status, $comment) {
		if( $old_status != $new_status ) {

			if( $new_status != 'spam' ) {

				$post_id = $comment->comment_post_ID;
				$post_url = get_permalink( $post_id );
				
				$feed =  $post_url . "feed";
				$commentfeed = $post_url . "comments/feed/";
				
				$settings = bunnycdnhtmlcaching_get_settings();
				$account_key = $settings['bunnycdn_api_key'];
				$pullzone_id = $settings['bunnycdn_pullzone_id'];
				$cdn = new bunnycdn_api();
				
				if ($account_key) {
					$cdn->Account( $account_key )->PurgeCache("", $pullzone_id);
					$cdn->Account( $account_key )->PurgeCache( $feed );
					$cdn->Account( $account_key )->PurgeCache( $commentfeedurl );
				}
				
			}
		}
	}	
	
	add_action( 'switch_theme', 'bunnycdnhtmlcaching_purge_everthing' );
	function bunnycdnhtmlcaching_purge_everthing() {

		$settings = bunnycdnhtmlcaching_get_settings();
		$account_key = $settings['bunnycdn_api_key'];
		$pullzone_id = $settings['bunnycdn_pullzone_id'];
		$cdn = new bunnycdn_api();
		
		if ($account_key && $pullzone_id) {
			$cdn->Account( $account_key )->PurgeCache("", $pullzone_id);
		}

		return false;
	}

	function bunnycdnhtmlcaching_modify_remote_ip() {
		if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
			$HTTP_X_FORWARDED = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
			$_SERVER['REMOTE_ADDR'] = $HTTP_X_FORWARDED[0];
		}		
	}

	add_action('init', 'bunnycdnhtmlcaching_modify_remote_ip');
	update_option( 'show_comments_cookies_opt_in', '' );

	function bunnycdnhtmlcaching_run_bunnycdn_setup() {

		$continue = true;

		if (is_admin()) {

			$new_zone = true;
			$non_www_host_exist = false;
			$www_host_exist = false;
			$site_version = bunnycdnhtmlcaching_get_site_version();

			$non_www_host = bunnycdnhtmlcaching_get_host('non-www');
			$www_host = bunnycdnhtmlcaching_get_host('www');

			$settings = bunnycdnhtmlcaching_get_settings();
			$server_ip = $_SERVER['SERVER_ADDR'];

			$zone_data = "";
			$zone_name = bunnycdnhtmlcaching_get_zone_name();

			if (bunnycdnhtmlcaching_is_valid_api_key($settings['bunnycdn_api_key'])) {
				
				// Zone Getting/Creating

				$cdn = new bunnycdn_api();
				$response = $cdn->Account($settings['bunnycdn_api_key'])->GetZoneList();

				if ($response['status'] == "success") {

					$zones = $response['zone_smry'];
					$bunnycdn_pullzone_id = $settings['bunnycdn_pullzone_id'] ? $settings['bunnycdn_pullzone_id'] : false;

					if (!$bunnycdn_pullzone_id && bunnycdnhtmlcaching_does_host_exist($zones)) {

						$continue = false;

					}

					if ($continue) {

						foreach($zones as $zone) {

							if ($zone['zone_name'] == $zone_name) {

								$zone_data = $zone;
								$new_zone = false;

								break;

							}

						}

					}


					if ($continue && $new_zone) {

						$response = $cdn->Account($settings['bunnycdn_api_key'])->CreateNewZone($zone_name, $_SERVER['REQUEST_SCHEME']  . "://" .  $server_ip);
						
						if ($response['status'] == "success") {

							$zone_data = $response;							

						} else {

							$message = "Oops! something went wrong, we are unable to create new pullzone!";
							if (is_admin()) {
								add_settings_error( 'bunnycdnhtmlcaching_settings', 200, $message, "error" );
							}

							$continue = false;

						}	

					}


				}


		        // Hosts Settings
		        $bunnycdn_pullzone_id = $settings['bunnycdn_pullzone_id'] ? $settings['bunnycdn_pullzone_id'] : false;

				if (!$bunnycdn_pullzone_id && $continue) {

					if (!$www_host_exist) {

						$host_name_url = $www_host;
						$add_host = $cdn->Account($settings['bunnycdn_api_key'])->AddHostName($zone_data['zone_id'], $host_name_url);

						if ($add_host['status'] == "success") {

							$www_host_setup = true;

						} else {

							$message = "Oops! something went wrong, we are unable add " . $host_name_url . " as a host!";
							if (is_admin()) {
								add_settings_error( 'bunnycdnhtmlcaching_settings', 200, $message, "error" );
							}

							$continue = false;
						}

					}

					if (!$non_www_host_exist) {

						$host_name_url = $non_www_host;
						$add_host = $cdn->Account($settings['bunnycdn_api_key'])->AddHostName($zone_data['zone_id'], $host_name_url);

						if ($add_host['status'] == "success") {

							$non_www_host_setup = true;

						} else {

							$message = "Oops! something went wrong, we are unable add " . $host_name_url . " as a host!";
							if (is_admin()) {
								add_settings_error( 'bunnycdnhtmlcaching_settings', 200, $message, "error" );
							}

							$continue = false;

						}
					}

				}

				if ($continue && $zone_data['zone_id']) {

					$settings = get_option("bunnycdnhtmlcaching_settings");
					$settings['bunnycdn_pullzone_id'] = $zone_data['zone_id'];
		    		update_option("bunnycdnhtmlcaching_settings", serialize($settings));	

				}

				if ($continue) {

					// Zone Settings
					$bunnycdn_webp_image_delivery = isset($settings['bunnycdn_webp_image_delivery']) ? $settings['bunnycdn_webp_image_delivery'] : 0;

					$request_parameters  = [
						"DisableCookies" => false,
						"CacheControlMaxAgeOverride" => 0,
						"CacheControlBrowserMaxAgeOverride" => 0,
						"EnableQueryStringOrdering" => true,
						"EnableWebpVary" => ($bunnycdn_webp_image_delivery == 2) ? true : false
					];

					$response = $cdn->UpdateZone($zone_data['zone_id'], $request_parameters);
					
					if ($response['status'] != "success") {

						$message = "Oops! something went wrong, we are unable to properly configure zone '" . $zone_data['zone_name'] . "'!";
						if (is_admin()) {
							add_settings_error( 'bunnycdnhtmlcaching_settings', 200, $message, "error" );
						}

						$continue = false;	

					}

					if ($continue) {

						$vary_cache_request_parameters = [
							'PullZoneId' 					=> $zone_data['zone_id'],
							'QueryStringVaryEnabled'  		=> true,
							'RequestHostnameVaryEnabled'  	=> false,
							'UserCountryCodeVaryEnabled' 	=> false,
							'WebpVaryEnabled'				=> ($bunnycdn_webp_image_delivery == 2) ? true : false
						];

						$response = $cdn->SetVaryCache($vary_cache_request_parameters);

						if ($response['status'] != "success") {

							$message = "Oops! something went wrong, we are unable to properly configure very cache of zone '" . $zone_data['zone_name'] . "'!";
							if (is_admin()) {
								add_settings_error( 'bunnycdnhtmlcaching_settings', 200, $message, "error" );
							}

							$continue = false;	

						}
					}


					if ($continue & $new_zone) {

						$request_parameters = [];

						$site_url = get_option("siteurl");
						$parse_url = parse_url($site_url);
						$path = isset($parse_url['path']) ? $parse_url['path'] : "";

						// Zone EdgeRule - Set Host
						$request_parameters[]  = [
							"ActionParameter1"		=>	"host",
							"ActionParameter2"		=>	$_SERVER['HTTP_HOST'],
							"Description"			=>	"Set Host",
							"Enabled"				=> 	true,
							"ActionType"			=>	6,				
							"TriggerMatchingType"	=> 	0,
							"Triggers" 				=> [
								[
									"Type"					=>	0,
									"PatternMatchingType"	=>	0,
									"Parameter1"			=>	"",
									"PatternMatches"		=>	["*"]
								]
							]				
						];

						// Zone EdgeRule - Canonical
						$url = $site_version == "www" ? $non_www_host : $www_host;
						$request_parameters[]  = [
							"ActionParameter1"		=>	$_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "{{path}}",
							"ActionParameter2"		=>	"",
							"Description"			=>	"Canonical",
							"Enabled"				=> 	true,
							"ActionType"			=>	1,				
							"TriggerMatchingType"	=> 	0,
							"Triggers" 				=> [
								[
									"Type"					=>	0,
									"PatternMatchingType"	=>	0,
									"Parameter1"			=>	"",
									"PatternMatches"		=>	[
										"*://" . $zone_name . ".b-cdn.net/*", 
										"*://" . $url . "/*",
										$_SERVER['REQUEST_SCHEME'] . "://" . $url . "/*",
									]
								]
							]				
						];

						// Zone EdgeRule - Always Cache Static Files even if user is logged in
						$request_parameters[]  = [
							"ActionParameter1"		=>	"2592000",
							"Description"			=>	"Always Cache Static Files",
							"Enabled"				=> 	true,
							"ActionType"			=>	3,				
							"TriggerMatchingType"	=> 	0,
							"Triggers" 				=> [
								[
									"Type"					=>	3,
									"PatternMatchingType"	=>	0,
									"PatternMatches"		=>	[ "css", "js", "svg", "png", "jp*g" ]										
								],
								[
									"Type"					=>	3,
									"PatternMatchingType"	=>	0,
									"PatternMatches"		=>	[ "woff*","ico", "webp", "gif", "mp4" ]
								]										
							]			
						];

						// Zone EdgeRule - Bypass Cache
						$request_parameters[]  = [
							"ActionParameter1"		=>	"2592000",
							"ActionParameter2"		=>	"",
							"Description"			=>	"Bypass Cache",
							"Enabled"				=> 	true,
							"ActionType"			=>	3,				
							"TriggerMatchingType"	=> 	2,
							"Triggers" 				=> [
								[
									"Type"					=>	0,
									"PatternMatchingType"	=>	0,
									"Parameter1"			=>	"",
									"PatternMatches"		=>	[
										"*/wp-admin/*",
										"*/wp-json/*",
										"*.php*",
										"*.xml", // Ignore XML Sitemap from caching
										"*/page/*"
									]
								],
								
								 
								 /* List of cookies purposefully ignored for bypassing caching
								 * wordpress_test_cookie = To serve cached response to 'logged out' user in same browser window.
								 * comment_author cookie = No need as 'show_comments_cookies_opt_in' is off due to perf and privacy reasons.
								 */
							   	
								
								[
									"Type"					=>	1,
									"PatternMatchingType"	=>	0,
									"Parameter1"			=>	"cookie",
									"PatternMatches"		=>	[  
										"*wp-postpass*",
										"*wordpress_logged_in*",
										"*woocommerce_cart_hash*",
										"*edd_items_in_cart*"
									]
								],
								[
									"Type"					=>	6,
									"PatternMatchingType"	=>	0,
									"Parameter1"			=>	"",
									"PatternMatches"		=>	[
										"s=*",
										"unapproved*"
									]
								]										
							]				
						];

						// Zone EdgeRule - Ignore Query Strings
						$request_parameters[]  = [
							"ActionParameter1"		=>	"",
							"ActionParameter2"		=>	"",
							"Description"			=>	"Ignore Query Strings",
							"Enabled"				=> 	true,
							"ActionType"			=>	11,				
							"TriggerMatchingType"	=> 	0,
							"Triggers" 				=> [
								[
									"Type"					=>	6,
									"Parameter1"			=> "",
									"PatternMatchingType"	=>	0,
									"PatternMatches"		=>	["*fbclid=*", "*utm_*", "*cn-reloaded*", "*ao-noptimize*", "*ref=*"]								
								]										
							]			
						];

						// Zone EdgeRule - Browser Cache
						$request_parameters[]  = [
							"ActionParameter1"		=>	"Cache-Control",
							"ActionParameter2"		=>	"public, max-age=31536000, immutable",
							"Description"			=>	"Browser Cache",
							"Enabled"				=> 	true,
							"ActionType"			=>	5,				
							"TriggerMatchingType"	=> 	0,
							"Triggers" 				=> [
								[
									"Type"					=>	3,
									"PatternMatchingType"	=>	0,
									"Parameter1"			=>	"",
									"PatternMatches"		=>	[
										"png",
										"css",
										"js",
										"jp*g",
										"woff*"
									]
								],
								[
									"Type"					=>	3,
									"PatternMatchingType"	=>	0,
									"Parameter1"			=>	"cookie",
									"PatternMatches"		=>	[
										"webp",
										"svg",
										"ttf"
									]
								]										
							]				
						];


						foreach ($request_parameters as $key => $request_parameter) {

							$response = $cdn->AddEdgeRule($zone_data['zone_id'], $request_parameter);

						}
					}

					// BunnyCDN Optimizer
					if ($continue && $bunnycdn_webp_image_delivery == 3) {
						$request_parameters = [
							"OptimizerAutomaticOptimizationEnabled"		=>	true,
							"OptimizerDesktopMaxWidth"					=>	"1600",
							"OptimizerEnableManipulationEngine"			=>	true,
							"OptimizerEnableWebP"						=>	true,
							"OptimizerEnabled"							=>	true,
							"OptimizerImageQuality"						=>	"85",
							"OptimizerMinifyCSS"						=>	true,
							"OptimizerMinifyJavaScript"					=>	true,
							"OptimizerMobileImageQuality"				=>	"70",
							"OptimizerMobileMaxWidth"					=>	"800",
							"OptimizerWatermarkEnabled"					=>	true,
							"OptimizerWatermarkMinImageSize"			=>	"300",
							"OptimizerWatermarkOffset"					=>	"3",
							"OptimizerWatermarkPosition"				=>	"0",
							"OptimizerWatermarkUrl"						=>	"",
							"PullZoneId"								=>	$zone_data['zone_id']
						];		

						$response = $cdn->SetOptimizerConfiguration($request_parameters);

						if ($response['status'] != "success") {
							
							$message = "Oops! something went wrong, we are unable to properly configure optimizer for the '" . $zone_data['zone_name'] . "' zone!";
							if (is_admin()) {
								add_settings_error( 'bunnycdnhtmlcaching_settings', 200, $message, "error" );
							}

							$continue = false;	

						}
					}

				}

			} else {
				$continue = false;
			}

		}

		return $continue;
	}	

	function bunnycdnhtmlcaching_dns_ssl_html() {

		$settings = bunnycdnhtmlcaching_get_settings();
		$www = bunnycdnhtmlcaching_get_host('non-www');
		$www_host = bunnycdnhtmlcaching_get_host('www');

		$bunnycdn_pullzone_id = isset($settings['bunnycdn_pullzone_id']) ? $settings['bunnycdn_pullzone_id'] : false;
		if ($bunnycdn_pullzone_id && !bunnycdnhtmlcaching_get_bunnycdn_ssl_certificate()) {
			echo '
				<h2>' . __('Install Free SSL', 'bunnycdnhtmlcaching') . '</h2>' . __('Before trying to install SSL, point below records at <a href="https://dash.cloudflare.com/?to=/:account/:zone/dns" target="_blank">Cloudflare DNS page </a>', 'bunnycdnhtmlcaching') . '
				<table id="cloudflare_dns_table" role="presentation">
				     <tbody>
				        <tr>
				           <th scope="row" width="10%">Record Type</th>
				           <th scope="row" width="15%">Name</th>
				           <th scope="row" width="20%">Value</th>
				           <th scope="row" width="10%">TTL</th>
				           <th scope="row" width="10%">Status</th>
				        </tr>
				        <tr>
				           <td>CNAME</td>
				           <td>' . $www_host . '</td>
				           <td>' . $www . '.b-cdn.net</td>
				           <td>Automatic</td>
				           <td>DNS only</td>
				        </tr>
				        <tr>
				           <td>CNAME</td>
				           <td>www</td>
				           <td>' . $www . '.b-cdn.net</td>
				           <td>Automatic</td>
				           <td>DNS only</td>
				        </tr>
				     </tbody>
				  </table>
				  <p class="submit"><a href="https://bunnycdn.com/dashboard/pullzones/edit/' . $bunnycdn_pullzone_id . '" target="_blank" id="install_ssl" class="button button-primary">' . __('Install SSL', 'bunnycdnhtmlcaching') . '</a></p>
				</form>
			';
		}

	}
		