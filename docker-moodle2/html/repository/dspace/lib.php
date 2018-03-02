<?php

class repository_dspace extends repository {

    public $rest_url = 'http://localhost:8080/rest/';
    
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array(), $readonly=TRUE) {
        parent::__construct($repositoryid, $context, $options, $readonly);
        $this->rest_url = $this->get_option('dspace_url');
    }

    /**
     * This repository only supports links to a DSpace instance
     * {@inheritDoc}
     * @see repository::supported_returntypes()
     */
    function supported_returntypes() {
        return FILE_EXTERNAL;
    }

    function supported_filetypes()   {
        return '*';
    }

    // Admin zone

    public static function get_type_option_names() {
        /*return array_merge(parent::get_type_option_names(), array('dspace_url'));*/
        $option_names = array_merge(parent::get_type_option_names(), array('dspace_url'));

        return $option_names;
    }

    public static function type_config_form($mform, $classname='repository') {
        parent::type_config_form($mform, $classname);
        
        
        $dspaceUrl = get_config('repository_dspace', 'dspace_url');
        $mform->addElement('text', 'dspace_url', get_string('dspace_url', 'repository_dspace'), array('value' => $dspaceUrl,'size' => '40'));
        $mform->setType('dspace_url', PARAM_TEXT);
    }

    // Listing

    function get_listing($path='', $page='') {
        global $OUTPUT;

        if (!empty($path)) {
            $pathArray = json_decode($path);
        } else {
            $pathArray = array(array( 'name' => '', 'type' => 'communities'));
        }
        
        $list = array();
        $list['nologin'] = true;
        $list['dynload'] = true;
        $list['nosearch'] = true;
        $list['list'] = array();
        
        $lastPath =  (array) end($pathArray);
        
        $results = $this->getChildrenByType ( $lastPath );
        
        foreach($results as $result) {
            $itemPath = $pathArray;
            $itemPath [] = array('name' => $result->name, 'id' => $result->uuid, 'type' => $result->type);
            $baseElement = array (
                    'title' => $result->name);
            
            switch($result->type) {
                case 'community':
                case 'collection':
                case 'item' :
                    $typeOptions = array (
                            'children' => array (),
                            'dynload' => true,
                            'thumbnail' => $OUTPUT->pix_url ( file_folder_icon ( 256 ) )->out ( false ),
                            'path' => json_encode ( $itemPath ) 
                    );
                    break;
		case 'bitstream':
                    $typeOptions = array (
                    'thumbnail' => $OUTPUT->pix_url(file_extension_icon($result->name, 256))->out(false),
                    'url' => $this->rest_url.'bitstreams/'.$result->uuid.'/retrieve',
                    'source' => $this->rest_url.'bitstreams/'.$result->uuid.'/retrieve');
                    break;
            }
	if($result->key && $result->key == 'dc.identifier'){

	    $baseElement = array (
		    'title' => 'LO URL:'.$result->value);

	    $typeOptions = array (
	    'thumbnail' => $OUTPUT->pix_url(file_extension_icon('.xml', 256))->out(false),
	    'url' => $result->value,
	    'source' => $result->value);
	}	

            $list ['list'] [] = array_merge($baseElement, $typeOptions);
        } 
        $list['path'] = array();
        
	while (count($pathArray) > 0) {
	    $lastItem =  (array) end($pathArray);
	    array_unshift($list['path'], array(
		    'path'=>json_encode($pathArray), 
		    'name'=> strlen($lastItem['name']) <= 20 ? $lastItem['name'] : substr($lastItem['name'], 0, 7).'...'));
	    array_pop($pathArray);
	    
	}
        return $list;
    }
    
    /**
     * Get children objects according to DSpace object type
     * Communities will retrieve subcommunities and collections
     * Collections will retrieve items
     * Items will retrieve bitstreams
     * 
     * @param path Path information about the current dspace object
     * 
     */
    private function getChildrenByType($path) {
        $apiCallResult = null;
        switch ($path['type']) {
            case 'community' :
                $query = 'communities/' . $path ['id'] . '/?expand=collections,subCommunities';
                $getChildren = function () use (&$apiCallResult) {
                    return array_merge ( $apiCallResult->collections, $apiCallResult->subcommunities );
                };
                break;
            case 'collection' :
                $query = 'collections/' . $path ['id'] . '/?expand=items';
                $getChildren = function () use (&$apiCallResult) {
                    return array_merge ( $apiCallResult->items );
                };
                break;
            case 'item' :
                $query = 'items/' . $path ['id'] . '/?expand=bitstreams,metadata';
                $getChildren = function () use (&$apiCallResult) {
			$metadata = $apiCallResult->metadata;
			$metadataArray = Array();
			foreach($metadata as $mtd){
				if($mtd->key == 'dc.identifier'){
					array_push($metadataArray, $mtd);
				}
			}
			$results = array_merge ($apiCallResult->bitstreams, $metadataArray);
		    return $results;
                };
                break;
            case 'communities' :
            default :
                $query = 'communities';
                $getChildren = function () use (&$apiCallResult) {
                    return $apiCallResult;
                };
                break;
        }
        
        $apiCallResult = $this->call_api('GET', $query);
	$childrenList = $getChildren();
        return $childrenList;
     }


    // REST

    function call_api($method, $endpoint, $data = false)
    {
    
        $curl = curl_init();

        $url = $this->rest_url .$endpoint;

        
        switch ($method)
        {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf('%s?%s', $url, http_build_query($data));
        } 

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	if (strpos($url, 'https') !== false) {
           curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	}
	

        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));

        $result = curl_exec($curl);

        curl_close($curl);
        return json_decode($result); 
    }
    
    public function set_option($options = array()) {
        if (! empty ( $options ['dspace_url'] )) {
            set_config ( 'dspace_url', trim ( $options ['dspace_url'] ), 'dspace' );
        }
        unset ( $options ['dspace_url'] );
        $ret = parent::set_option ( $options );
        return $ret;
    }
    
    public function get_option($config = '') {
        if (preg_match ( '/^dspace_/', $config )) {
            return trim ( get_config ( 'dspace', $config ) );
        }
        
        $options = parent::get_option ( $config );
        return $options;
    }

    /**
    * External reference section
    */
    public function get_reference_file_lifetime($ref) {
        return 60 * 60 * 24; // One day
    }
    public function send_file($stored_file, $lifetime = 86400, $filter = 0, $forcedownload = false, array $options = null) {
        $url = $stored_file->get_reference ();
        if ($url) {
            header ( 'Location: ' . $url );
        } else {
            send_file_not_found ();
        }
    }
    
}

