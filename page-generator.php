<?php
/**
* Plugin Name: Page Generator
* Plugin URI: http://www.wpcube.co.uk/plugins/page-generator
* Version: 1.0.1
* Author: WP Cube
* Author URI: http://www.wpcube.co.uk
* Description: Generate multiple Pages using dynamic content
* License: GPL2
*/

/*  Copyright 2013 WP Cube (email : support@wpcube.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

ob_start();

/**
* Page Generator Class
* 
* @package WP Cube
* @subpackage Page Generator
* @author Tim Carr
* @version 1.0.1
* @copyright WP Cube
*/
class PageGenerator {
    /**
    * Constructor. Acts as a bootstrap to load the rest of the plugin
    */
    function PageGenerator() {
    	global $pgKeywords;
    	
        // Plugin Details
        $this->plugin = new stdClass;
        $this->plugin->name = 'page-generator'; // Plugin Folder
        $this->plugin->settingsName = 'page-generator';
        $this->plugin->displayName = 'Page Generator'; // Plugin Name
        $this->plugin->version = '1.0.1'; // The version of this plugin
        $this->plugin->folder = WP_PLUGIN_DIR.'/'.$this->plugin->name; // Full Path to Plugin Folder
        $this->plugin->url = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); // Ful URL to Plugin folder
        $this->plugin->subPanels = array(__('Generate'));
        $this->plugin->upgradeReasons = array(
        	array(__('Generate Posts, Pages or Custom Post Types'), __('Generate Posts, Pages and any registered Custom Post Types.')),
        	array(__('Taxonomies'), __('Choose taxonomy terms for Posts and Custom Post Types.')),
        	array(__('Automatically build nearby cities keywords'), __('Enter a city name, country and radius to automatically build a keyword containing all nearby cities.')),
        	array(__('Google Maps Block'), __('Insert a Google Maps content block.')),
        	array(__('Wikipedia Block'), __('Insert content from a Wikipedia article.')),
        	array(__('Yelp Local Businesses Block'), __('Insert a list of local businesses listed on Yelp.')),
        	array(__('Featured Image'), __('Choose to include your featured image on each Page, Post or CPT.')),
        );
        $this->plugin->upgradeURL = 'http://www.wpcube.co.uk/plugins/page-generator-pro';
        
        // Dashboard Submodule
        if (!class_exists('WPCubeDashboardWidget')) {
			require_once($this->plugin->folder.'/_modules/dashboard/dashboard.php');
		}
		$dashboard = new WPCubeDashboardWidget($this->plugin); 
		
		// Models
		if(!class_exists('WP_List_Table')) require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
		require_once($this->plugin->folder.'/models/keywords-table.php');
		require_once($this->plugin->folder.'/models/keywords.php');
		$pgKeywords = new PGKeywords();

		// Hooks
        add_action('admin_enqueue_scripts', array(&$this, 'adminScriptsAndCSS'));
        add_action('admin_menu', array(&$this, 'adminPanelsAndMetaBoxes'));
        add_action('plugins_loaded', array(&$this, 'loadLanguageFiles'));
    }
    
    /**
    * Activation routines
    */
    function activate() {
    	global $wpdb;

        // Create database tables
        $wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."page_generator_keywords (
							`keywordID` int(10) NOT NULL AUTO_INCREMENT,
							`keyword` varchar(200) NOT NULL,
							`data` text NOT NULL,
							PRIMARY KEY `keywordID` (`keywordID`),
							UNIQUE KEY `keyword` (`keyword`)
						) ENGINE=MyISAM 
						DEFAULT CHARSET=".$wpdb->charset."
                        AUTO_INCREMENT=1"); 	
    }
    
    /**
    * Deactivation routines
    * Note: these will also run on a plugin upgrade!
    */
    function deactivate() {
    	
    }
    
    /**
    * Register and enqueue any JS and CSS for the WordPress Administration
    */
    function adminScriptsAndCSS() {
    	// JS
    	wp_enqueue_script($this->plugin->name.'-admin', $this->plugin->url.'js/admin.js', array('jquery'), $this->plugin->version, true);
    	        
    	// CSS
        wp_enqueue_style($this->plugin->name.'-admin', $this->plugin->url.'css/admin.css', array(), $this->plugin->version); 
    }
    
    /**
    * Register the plugin settings panel
    */
    function adminPanelsAndMetaBoxes() {
        add_menu_page($this->plugin->displayName, $this->plugin->displayName, 'manage_options', $this->plugin->name, array(&$this, 'adminPanel'), 'dashicons-format-aside');
        add_submenu_page($this->plugin->name, __('Keywords', $this->plugin->name), __('Keywords', $this->plugin->name), 'manage_options', $this->plugin->name, array(&$this, 'adminPanel'));
        
        foreach ($this->plugin->subPanels as $key=>$subPanel) {
            add_submenu_page($this->plugin->name, $subPanel, $subPanel, 'manage_options', $this->plugin->name.'-'.str_replace(' ', '-', strtolower($subPanel)), array(&$this, 'adminPanel'));    
        }
    }
    
	/**
    * Output the Administration Panel
    * Save POSTed data from the Administration Panel into a WordPress option
    */
    function adminPanel() {
    	global $pgKeywords;
    	
    	// Check command to determine what to output
		switch (strtolower(str_replace($this->plugin->name.'-', '', $_GET['page']))) {
    		case 'generate':
    			// Generate
    			
    			// Save
		        if (isset($_POST['submit'])) {
		        	if (isset($_POST[$this->plugin->name])) {
		        		// Map POST data to array
		        		$settings = $_POST[$this->plugin->name];
		        		$settings['content'] = $_POST['content']; // TinyMCE doesnt support array
		        		
		        		// Save Settings
		        		update_option($this->plugin->settingsName, $settings);
		        		
		        		// Read value of submit button
		        		switch ($_POST['submit']) {
		        			case __('Save', $this->plugin->name):
		        				// Save only
		        				$this->message = __('Settings Updated.', $this->plugin->name);
		        				break;
		        			case __('Test', $this->plugin->name):
		        				// Test
		        				// 1 x Page/Post in Draft Mode
		        				$result = $this->generate(true);
		        				if (is_wp_error($result)) {
		        					$this->message = $result->get_error_message();
		        				} else {
		        					$this->message = __('Test Page Generated.', $this->plugin->name).' <a href="'.$result.'" target="_blank" class="button">'.__('View Test', $this->plugin->name).'</a>';
		        				}
		        				break;
		        			case __('Generate', $this->plugin->name):
		        				// Generate
		        				// All Pages/Posts
		        				$result = $this->generate(false);
		        				if (is_wp_error($result)) {
		        					$this->message = $result->get_error_message();
		        				} else {
		        					$this->message = __('Pages Generated.', $this->plugin->name);
		        				}
		        				break;
		        		}
						
					}
		        }
		        
		        // Get all available keywords and post types
		        $keywords = $pgKeywords->getAll('keyword', 'ASC', 1, 999, '', true);
		        $types = get_post_types();
		        $ignoredPostTypes = array('attachment','revision','nav_menu_item'); 
		        $taxonomies = get_taxonomies('', 'objects');
		        $ignoredTaxonomies = array('nav_menu','link_category','post_format');
		        
		        // Get latest settings
        		$this->settings = get_option($this->plugin->settingsName);
		        $view = 'views/generate.php';
		        
    			break;
    			
    		default:
    			// Keywords
    			$cmd = ((isset($_GET['cmd'])) ? $_GET['cmd'] : '');
            	
            	switch ($cmd) {
            		case 'edit':
                        // Check data
                        if (isset($_POST['submit'])) { 
                        	// Map post data back to keyword, in case we need it in the form again due to form validation failure
                            $this->keyword = $pgKeywords->transformPostData($_POST);

                            // Save data and check result
                            $result = $pgKeywords->save($this->keyword);
                            if (is_wp_error($result)) {
                                $this->errorMessage = $result->get_error_message();
                                $this->keyword = $pgKeywords->parse($this->keyword); // Strips slashes
                            } else {
                                $this->message = __('Keyword '.($_GET['cmd'] == 'add' ? 'created' : 'updated'), $this->plugin->name);
                                $this->keyword = $pgKeywords->getByID($_GET['pKey']); // Get updated keyword from DB
                            }
                        } else {
                            if (isset($_GET['pKey'])) $this->keyword = $pgKeywords->getByID($_GET['pKey']);
                        }
                        
                        // If redirected from add to edit, show the user the keyword was created
                        if (isset($_GET['msg'])) $this->message = __('Keyword created.', $this->plugin->name);
                        
                        // View
                        $view = 'views/keywords-form.php';
                        
                        break;
                    case 'delete':
                        // Delete single
                        $result = $pgKeywords->deleteByID($_GET['pKey']);
                        if (is_wp_error($result)) {
                            $this->errorMessage = $result->get_error_message();
                        } else {
                            $this->message = __('Keyword deleted.', $this->plugin->name);
                        }
                        
                        // Include PGKeywords_List_Table class files
                        require_once(WP_PLUGIN_DIR.'/'.$this->plugin->name.'/models/keywords-table.php');
                        $this->wpListTable = new PGKeywords_List_Table();

                        // View
                        $view = 'views/keywords-table.php';
                        
                        break;
                    case 'add':
                    	if (isset($_POST['submit'])) {
                            // Map post data back to keyword, in case we need it in the form again due to form validation failure
                            $this->keyword = $pgKeywords->transformPostData($_POST);
                            
                            // Save data and check result
                            $result = $pgKeywords->Save($this->keyword);
                            if (is_wp_error($result)) {
                                $this->errorMessage = $result->get_error_message();
                                $this->keyword = $pgKeywords->parse($this->keyword); // Strips slashes
                            } else {
                            	// Redirect to edit
                            	header('Location: admin.php?page='.$this->plugin->name.'&cmd=edit&pKey='.$result.'&msg=1');
                                die();
                            }
                        } else {                    
	                        // Set some defaults
	                        $this->keyword = array();
	                        $this->keyword['keyword'] = '';
	                        $this->keyword['data'] = '';
                        }
                        
                        // View
                        $view = 'views/keywords-form.php';
                        
                        break;
                    default:                        
                        // Bulk Actions
                        $action = '';
                        if (isset($_POST['action2']) AND $_POST['action2'] != '-1') {
                        	$action = $_POST['action2'];
                        } else if (isset($_POST['action']) AND $_POST['action'] != '-1') {
                        	$action = $_POST['action'];
                        }
                        
                        switch ($action) {
                            case 'delete':
                                $result = $pgKeywords->deleteByIDs($_POST['keywordIDs']);
                                if (is_wp_error($result)) {
                                    $this->errorMessage = $result->get_error_message();
                                } else {
                                    $this->message = __('Keywords deleted.', $this->plugin->name);
                                }
                                break; 
                        }  
                        
                        // Include PGKeywords_List_Table class files
                        require_once(WP_PLUGIN_DIR.'/'.$this->plugin->name.'/models/keywords-table.php');
                        $this->wpListTable = new PGKeywords_List_Table();
                        
                        // View
                        $view = 'views/keywords-table.php';
                        
						break;    
                }
                
                // Get latest settings
        		$this->settings = get_option($this->plugin->settingsName);
                break;
    	}
    	
		// Load Settings Form
        include_once($this->plugin->folder.'/'.$view);  
    }
    
    /**
	* Loads plugin textdomain
	*/
	function loadLanguageFiles() {
		load_plugin_textdomain($this->plugin->name, false, $this->plugin->name.'/languages/');
	}
    
    /**
    * Main function to generate Pages
    *
    * @param bool $testMode Test Mode
    * @return mixed WP_Error | test URL | true
	*/
    function generate($testMode = true) {
    	global $pgKeywords;
    	
    	// Get latest settings
        $settings = get_option($this->plugin->settingsName);
        $originalSettings = $settings;
        
        // Get required keywords that need replacing with data, across all settings fields
        $requiredKeywords = $this->findKeywordsInContent($settings);
       	if (count($requiredKeywords) == 0) return new WP_Error('keyword_error', __('No keywords were specified in the title, content or excerpt.', $this->plugin->name));
       	
       	// Get keyword data for each keyword found above, and determine the maximum number of Posts
       	// we can create
       	$keywords = array();
       	$currentKeywordIndex = array();
       	$maxNumberOfPosts = 0;
       	foreach ($requiredKeywords as $keyword) {
       		$keywordArr = $pgKeywords->getBy('keyword', $keyword);
       		$maxNumberOfPosts = ((count($keywordArr['dataArr']) > $maxNumberOfPosts) ? count($keywordArr['dataArr']) : $maxNumberOfPosts);
       		$keywords[$keyword] = $keywordArr;
       		$currentKeywordIndex[$keyword] = 0;
       	}
       	
       	// Determine the actual number of Posts to create:
       	// - Test Mode: only ever create 1 Post
       	// - non-Test Mode: create either the setting's number of posts, or the maximum number based on the shortcodes
       	if ($testMode) {
       		$numberOfPosts = 1;
    	} else {
    		$numberOfPosts = (($settings['numberOfPosts'] > $maxNumberOfPosts) ? $maxNumberOfPosts : $settings['numberOfPosts']);
    	}
    	
    	// If rotating authors is enabled, get all admins, editors + authors
    	if (isset($settings['rotateAuthors'])) {
	    	$admins = $this->getUsersByRole('administrator');
	    	$editors = $this->getUsersByRole('editor');
	    	$authors = $this->getUsersByRole('author');
	    	$users = array_merge($admins, $editors, $authors);
	    	$currentUserIndex = 0;
    	}
    	
    	// Go through numberOfPosts, generating Posts/Pages/CPTs
    	for ($i = 0; $i < $numberOfPosts; $i++) { 
    		foreach ($keywords as $keyword=>$keywordArr) {
    			// Go through each field, replacing {keyword} with keyword 
    			foreach ($settings as $key=>$value) {
    				$index = $currentKeywordIndex[$keyword];
    				$replacement = $keywordArr['dataArr'][$index];
    				$settings[$key] = str_replace('{'.$keyword.'}', trim($replacement), $value);	
    			}
    			
    			// Increment index for next run,
    			// or reset if gone through all keywords
    			$currentKeywordIndex[$keyword]++;
    			if ($currentKeywordIndex[$keyword] > (count($keywordArr['dataArr'])-1)) {
    				$currentKeywordIndex[$keyword] = 0;
    			}
    		}
    		
    		// Create Page, Post or CPT
			$postID = wp_insert_post(array(
				'post_type' => 'page',
				'post_name' => strtolower($settings['permalink']),
				'post_title' => $this->spinContent($settings['title']),
				'post_content' => $this->spinContent($settings['content']),
				'post_status' => ($testMode ? 'draft' : $settings['status']),
				'post_author' => (isset($settings['rotateAuthors']) ? $users[$currentUserIndex] : $settings['author']), // ID
				'comment_status' => (isset($settings['comments']) ? 'open' : 'closed'),
				'ping_status' => (isset($settings['trackbacks']) ? 'open' : 'closed'),
			), true);
			
			if (is_wp_error($postID)) {
				return $postID;
			}
			
			// Page Template
			update_post_meta($postID, '_wp_page_template', $settings['pageTemplate']);
			
			// Increment author index for next run,
			// or reset if gone through all authors
			if (isset($settings['rotateAuthors'])) {
				$currentUserIndex++;
				if ($currentUserIndex > (count($users)-1)) {
					$currentUserIndex = 0;
				}
			}
			
			// Reset settings for next run
			$settings = $originalSettings;
    	}
    	
    	// Done!
    	if ($testMode) {
    		// Return URL
    		return get_bloginfo('url').'?page_id='.$postID.'&preview=true';
    	}
    	
    	return true;
    }
    
    /**
    * Recursively goes through an array, finding any {keywords} and spins
    * specified, to build up an array of keywords we need to fetch
    *
    * @param mixed $contentArr Content Array
    * @return array Required Keywords
    */
    function findKeywordsInContent($contentArr) {
    	global $pgKeywords;
    	
    	// Get all keywords
    	$keywords = $pgKeywords->getAll('keyword', 'ASC', 1, 999, '', true);
    	$requiredKeywords = array();
    	
    	// Iterate through array, finding keyword instances
    	foreach ($contentArr as $key=>$value) {
    		if (is_array($value)) continue; // Skip sub keys that are arrays i.e. taxonomies
    		
    		preg_match_all("|{(.+?)}|", $value, $matches); // Get keywords and spins
            if (!is_array($matches) OR count($matches[1]) == 0) continue; // No matches found
			
			foreach ($matches[0] as $mKey=>$keyword) {
                if (strpos($keyword, "|") !== false) continue; // Ignore spins
                
                // If this keyword is not in our requiredKeywords array, add it
                if (!in_array($matches[1][$mKey], $requiredKeywords)) {
                	$requiredKeywords[] = $matches[1][$mKey];
                }
            }
        }
        
        return $requiredKeywords; 	
    }
    
    /**
    * Checks if any spins are included in the content, and if so spins it
	*
	* @param mixed $string
    * @param mixed $seedPageName
    * @param mixed $openingConstruct
    * @param mixed $closingConstruct
    */
    function spinContent($string, $openingConstruct = '{', $closingConstruct = '}') {
        if(strpos($string, $openingConstruct) === false) return $string; // If we have nothing to spin return content 

        // Find all positions of the starting and opening braces
        $startPositions = $this->strpos_all($string, $openingConstruct);
        $endPositions   = $this->strpos_all($string, $closingConstruct);

        // There must be the same number of opening constructs to closing ones
        if($startPositions === false OR count($startPositions) !== count($endPositions)) return $string;

        $openingConstructLength = mb_strlen($openingConstruct);
        $closingConstructLength = mb_strlen($closingConstruct);

        // Organise the starting and opening values into a simple array showing orders
        foreach($startPositions as $pos) $order[$pos] = 'open';
        foreach($endPositions as $pos) $order[$pos] = 'close';
        ksort($order);

        // Go through the positions to get the depths
        $depth = 0;
        $chunk = 0;
        foreach($order as $position => $state) {
            if($state == 'open') {
                $depth++;
                $history[] = $position;
            } else {
                $lastPosition   = end($history);
                $lastKey        = key($history);
                unset($history[$lastKey]);

                $store[$depth][] = mb_substr($string, $lastPosition + $openingConstructLength, $position - $lastPosition - $closingConstructLength);
                $depth--;
            }
        }
        krsort($store);

        // Remove the old array and make sure we know what the original state of the top level spin blocks was
        unset($order);
        $original = $store[1];

        // Move through all elements and spin them
        foreach($store as $depth => $values) {
            foreach($values as $key => $spin) {
                # Get the choices
                $choices = explode('|', $store[$depth][$key]);
                $replace = $choices[mt_rand(0, count($choices) - 1)];

                # Move down to the lower levels
                $level = $depth;
                while($level > 0) {
                    foreach($store[$level] as $k => $v) {
                        $find = $openingConstruct.$store[$depth][$key].$closingConstruct;
                        if($level == 1 AND $depth == 1)
                        {
                            $find = $store[$depth][$key];
                        }
                        $store[$level][$k] = $this->str_replace_first($find, $replace, $store[$level][$k]);
                    }
                    $level--;
                }
            }
        }

        // Put the very lowest level back into the original string
        foreach($original as $key => $value) $string = $this->str_replace_first($openingConstruct.$value.$closingConstruct, $store[1][$key], $string);

        return $string;
    }
    
    /**
    * Similar to str_replace, but only replaces the first instance of the needle 
    * 
    * @param string $find Find
    * @param string $replace Replace
    * @param string $string Content
    * @return Amended String
    */
    function str_replace_first($find, $replace, $string) {
        if(!is_array($find)) $find = array($find); // Not an array
        if(!is_array($replace)) $replace = array($replace); // Not an array

        foreach($find as $key => $value) {
            if(($pos = strpos($string, $value)) !== false) {
                if(!isset($replace[$key])) $replace[$key] = ''; // If we have no replacement make it empty 
                $string = mb_substr($string, 0, $pos).$replace[$key].mb_substr($string, $pos + mb_strlen($value));
            }
        }

        return $string;
    }

    /**
    * Finds all instances of a needle in the haystack and returns the array.
    * 
    * @param string $haystack Haystack
    * @param string $needle Needle
    * @return array Positions
    */
    function strpos_all($haystack, $needle) {
        $offset = 0;
        $i      = 0;
        $return = false;
   
        while(is_integer($i)) {  
            $i = strpos($haystack, $needle, $offset);
       
            if(is_integer($i)) {
                $return[]   = $i;
                $offset     = $i + mb_strlen($needle);
            }
       }

       return $return;
    }
    
    /**
    * Returns an array of User IDs by role
    *
    * @param string $user User
    * @return array User IDs
    */
    function getUsersByRole($role) {
    	$usersArr = array();
    	$users = get_users(array(
			'role' => $role,	
    	));
    	if (count($users) > 0) {
    		foreach ($users as $user) {
    			$usersArr[] = $user->ID;
    		}
    	}
    	
    	return $usersArr;
    }
}

$pageGenerator = new PageGenerator;

// Activation + deactivation hooks - need to be outside of the class in order to function
register_activation_hook(__FILE__, array(&$pageGenerator, 'activate'));
register_deactivation_hook(__FILE__, array(&$pageGenerator, 'deactivate'));
?>
