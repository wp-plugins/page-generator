<?php
/**
* Keywords Model
* 
* @package WP Cube
* @subpackage Page Generator Pro
* @author Tim Carr
* @version 1.0
* @copyright WP Cube
*/   
class PGKeywords {

	/**
	* Primary SQL Table
	*/
	var $primaryTable = 'page_generator_keywords';
	
	/**
	* Primary SQL Table Primary Key
	*/
	var $primaryTableKey = 'keywordID';
	
    /**
    * Returns an array of records
    * 
    * @param string $orderBy Order By Column (default: name, optional)
    * @param string $order Order Direction (default: ASC, optional)
    * @param int $paged Pagination (default: 1, optional)
    * @param int $resultsPerPage Results per page (default: 5, optional)
    * @param string $search Search Keywords (optional)
    * @param bool $getAll Get all results (ignore pagination, optional)
    * @return array Records
    */
    function getAll($orderBy = 'keyword', $order = 'ASC', $paged = 1, $resultsPerPage = 10, $search = '', $getAll = false) {
        global $wpdb;
        
        // Check in case empty parameters have been sent
        if (empty($orderBy)) $orderBy = 'keyword';
        if (empty($order)) $order = 'ASC';
        if (empty($paged)) $paged = 1;
        if (empty($resultsPerPage)) $resultsPerPage = 10;
        
        $results = $wpdb->get_results(" SELECT *
                                    	FROM ".$wpdb->prefix.$this->primaryTable."
                                    	".(!empty($search) ? " WHERE keyword LIKE '%".mysql_real_escape_string($search)."%' " : "")."
                                    	ORDER BY ".mysql_real_escape_string($orderBy)." ".mysql_real_escape_string($order).
                                    	(!$getAll ? " LIMIT ".(($paged - 1) * $resultsPerPage).",".$resultsPerPage : ""));
            
      	return $results;
    }
    
    /**
    * Checks if any records exist
    *
    * @return bool Exists
    */
    function hasRecords() {
    	global $wpdb;
    	
    	$count = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.$this->primaryTable);
    	return (($count > 0) ? true : false);
    } 
    
    /**
    * Returns a count of the total number of records
    * 
    * @param string $search Search Keywords (optional)
    * @return int Record Count
    */
    function getTotal($search = '') {
        global $wpdb;
        
        if (!empty($search)) {
        	$results = $wpdb->get_results(" SELECT *
        	                                FROM ".$wpdb->prefix.$this->primaryTable."
        	                                WHERE keyword LIKE '%".mysql_real_escape_string($search)."%'");
        } else {
        	$results = $wpdb->get_results(" SELECT *
        	                                FROM ".$wpdb->prefix.$this->primaryTable);
        }
        return count($results);    
    }
    
    /**
    * Transforms POSTed data before saving to the database.
    *
    * @param array $postData $_POST data
    * @return array Normalised keyword Array
    */
    function transformPostData($postData) {
    	$keyword = $postData['page-generator']; // Main settings
    	if ($keyword['pKey'] != '') $keyword['keywordID'] = $keyword['pKey']; // Primary Key
        
       	return $keyword;
    }
    
    /**
    * Parses the given keyword data for frontend output, removing slashes
    *
    * @param array $keyword Keyword
    * @return array Parsed keyword
    */
    function parse($keyword) {
    	// Stripslashes and HTML entity encode characters that haven't already been encoded
    	if (isset($keyword['keyword'])) $keyword['keyword'] = htmlspecialchars(stripslashes($keyword['keyword']), ENT_QUOTES, 'UTF-8', false);
    	if (isset($keyword['data'])) $keyword['data'] = htmlspecialchars(stripslashes($keyword['data']), ENT_QUOTES, 'UTF-8', false);
    	
       	return $keyword;
    }

    /**
    * Returns a record with details for the given primary key ID
    * 
    * @param int $primaryKey Primary Key ID
    * @param bool $isFrontend Edits opt in code based on element settings and gets image sizes for elements (default: false)
    * @return array Record Details
    */
    function getByID($primaryKey) {
        global $wpdb;

        // Get record
        $results = $wpdb->get_results(" SELECT *
                                        FROM ".$wpdb->prefix.$this->primaryTable."
                                        WHERE ".$this->primaryTableKey." = '".mysql_real_escape_string($primaryKey)."'
                                        LIMIT 1", ARRAY_A);
        if (count($results) == 0) return false;
        $result = $results[0];  

        return $this->parse($result);
    }
    
    /**
    * Returns a record with details for the given key/value pair
    * 
    * @param string $field Field
    * @param string $value Value
    * @return array Record Details
    */
    function getBy($field, $value) {
        global $wpdb;

        // Get record
        $results = $wpdb->get_results(" SELECT *
                                        FROM ".$wpdb->prefix.$this->primaryTable."
                                        WHERE ".$field." = '".mysql_real_escape_string($value)."'
                                        LIMIT 1", ARRAY_A);
        if (count($results) == 0) return false;
        $result = $results[0];  
        
        // Expand data into array
        $result['dataArr'] = explode("\n", $result['data']);

        return $this->parse($result);
    }
    
    /**
    * Adds or edits a record, based on the given data array.
    * 
    * Must include pKey POST key if editing an existing record
    * 
    * @param array $data POST data
    * @return mixed object ID or WP_Error
    */
    function save($data) {
        global $wpdb;
        
        // Validate form data
        if (empty($data['keyword'])) return new WP_Error('form_error', __('Please enter a keyword.'));
        
        // If an import file is being uploaded, add this to the start of the data
        if (isset($_FILES['file'])) {
            // Check it is a valid type
            if ((!empty($_FILES['file']['type']) AND preg_match('/(text|txt|csv)$/i', $_FILES['file']['type'])) OR preg_match( '/(text|txt|csv)$/i', $_FILES['file']['name'])) {
                // Get file contents
                $handle = fopen($_FILES['file']['tmp_name'], "r");
                $contents = fread($handle, filesize($_FILES['file']['tmp_name']));
                fclose($handle);

                // If CSV, convert to newlines
                if (strpos($_FILES['file']['type'], 'csv') !== false) {
                    $lines = explode(",", $contents); 
                    $contents = implode("\n", $lines); 
                }
                
                // Add / append data
                $data['data'] .= ((strlen($data['data']) > 0) ? "\n".$contents : $contents);  
            }
        }
        
        if (empty($data['data'])) return new WP_Error('form_error', __('Please enter some keyword data, or import some using a CSV or text file.'));
     
        // Check whether we are adding or editing a record
        $results = '';
        if (isset($data['pKey'])) {
        	$results = $this->GetByID($data['pKey']);
        }
        if (!empty($results) AND count($results) > 1) {
            // Editing an existing record
            $result = $wpdb->query("UPDATE ".$wpdb->prefix.$this->primaryTable."
		                            SET keyword = '".htmlentities($data['keyword'], ENT_QUOTES)."',
		                            data = '".htmlentities($data['data'], ENT_QUOTES)."'
		                            WHERE ".$this->primaryTableKey."=".mysql_real_escape_string($data['pKey']));

            // Check query was successful
            if ($result === FALSE) return new WP_Error('db_query_error', __('Keyword could not be edited in the database. DB said: '.$wpdb->last_error), $wpdb->last_error); 

            // Success!
            return $data['pKey']; 
        } else {
            // Adding a new record   
            // Check keyword does not already exist
            $results = $wpdb->get_results(" SELECT *
                                        	FROM ".$wpdb->prefix.$this->primaryTable."
                                        	WHERE keyword = '".htmlentities($data['keyword'], ENT_QUOTES)."'
                                        	LIMIT 1", ARRAY_A);
			if (count($results) > 0) {
				return new WP_Error('db_query_error', __('Keyword already exists. Please use a unique shortname name for each keyword.'));
			}
                
            // Insert
            $result = $wpdb->query("INSERT INTO ".$wpdb->prefix.$this->primaryTable." (keyword, data)
            						VALUES ('".htmlentities($data['keyword'], ENT_QUOTES)."',
            						'".htmlentities($data['data'], ENT_QUOTES)."')");

            // Check query was successful
            if ($result === FALSE) return new WP_Error('db_query_error', __('Keyword could not be saved to the database. DB said: '.$wpdb->last_error), $wpdb->last_error); 
            $keywordID = $wpdb->insert_id;

            // Success!
            return $keywordID;
        }    
    }
      
    /**
    * Deletes the record for the given primary key ID
    * 
    * @param int $primaryKeys Primary Key ID
    * @return bool Success
    */
    function deleteByID($primaryKey) {
        global $wpdb;
        
        $result = $wpdb->query("DELETE FROM ".$wpdb->prefix.$this->primaryTable."
                        		WHERE ".$this->primaryTableKey." = ".mysql_real_escape_string($primaryKey)."
                        		LIMIT 1");
                          
        // Check query was successful
        if ($result === FALSE) return new WP_Error('db_query_error', __('Keyword could not be deleted from the database.'), $wpdb->last_error);

        return true;
    }
    
    /**
    * Deletes the records for the given primary key ID array
    * 
    * @param array $primaryKeys Primary Key ID array
    * @return bool Success
    */
    function deleteByIDs($primaryKeys) {
        global $wpdb;
        
        $result = $wpdb->query("DELETE FROM ".$wpdb->prefix.$this->primaryTable."
                        		WHERE ".$this->primaryTableKey." IN (".implode(',', $primaryKeys).")");
                          
        // Check query was successful
        if ($result === FALSE) return new WP_Error('db_query_error', __('Keywords could not be deleted from the database.'), $wpdb->last_error); 

        return true;
    }
}
?>
