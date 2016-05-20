<?php
session_start();
require_once('lib.php');
require_once(PROJECT_ROOT.'php/connection.php');

if (isset($_SESSION['userid'])) {

    $user = R::load('user', $_SESSION['userid']);

    if (isset($_POST['type'])) {

        $type = $_POST['type'];

        if (strpos($type, "category") !== false) {
			
        	$data = array();
        	$error = false;
        	$files = array();
        		 
        	foreach($_FILES as $file) {
        		move_uploaded_file($file['tmp_name'], "../icons/" .strtolower($_POST['name']) . ".ico");
        			 
        	}

			if (empty(R::find('category', ' name LIKE ? ', [$_POST['name']] ))) {

        		$category = R::dispense("category");
        		$category->name = $_POST['name'];
        		R::store($category);
        		
        	}     	        	

        } else {

            $object = R::dispense($type);
            $object->import($_POST, implode(", ", $categories[$_POST['type']]));
            $user['own' . ucfirst($type) . 'List'][] = encryptModel($object, $_SESSION['masterHash']);
            R::store($user);
        }

    } else if (isset($_GET['_'])) {
    	
    	if (isset($_GET['request'])) {
    		
    		if (strpos($_GET['request'], 'categories') !== false) {
    		
    			$categories = [];
    			
    			$categoryObjects = R::getAll( 'SELECT * FROM category' );
    			foreach($categoryObjects as $category) {
    				$categories[$category['id']] = $category['name'];
    			}    		
    			echo json_encode($categories);
    		
    		}
    	} else {

	        $modifiedAccounts = null;
	
	        $type = ucfirst($_GET['category']);
	
	        foreach ($user['own'.$type.'List'] as $object) {
	
	            $object =  decryptModel($object, $_SESSION['masterHash']);
	
	            $properties = $object->getProperties();
	            $keys = array_keys($properties);
	
	
	            $ignoredProperties = ['id', 'category', 'settings', 'url'];
	            for ($i = 0; $i < sizeof($properties); $i++) {
	                if (!in_array($keys[$i], $ignoredProperties)){
	                    $value = $object[$keys[$i]];
	                    $object[$keys[$i]] = '<div class="copy" data-clipboard-text="' . $value . '">' . $value . '</div>';
	                }
	            }
	
	            $settings = "<a class='editAccount' id='$object->id' href='#'><button class=\"edit btn btn-sm btn-info\"><span class=\"glyphicon glyphicon-pencil\"></span></button></a>";
	            $settings .= "                     ";
	            $settings .= "<a href='php/datahandler.php?mode=remove&id=$object->id&type=$type'><button class=\"delete btn btn-sm btn-danger\"><span class=\"glyphicon glyphicon-trash\"></span></button></a>";
	            $object->settings = $settings;

	            if (strpos($type, "Login") !== false) $object = parseUrl($object);
	            if (strpos($type, "Device") !== false) $object = addCategoryIcon($object);
	
	            $modifiedAccounts[] = $object;
	        }
	
	        if ($modifiedAccounts) {
	            echo json_encode($modifiedAccounts);
	        } else {
	            echo json_encode(new stdClass());
	        }
    	}

    } else if (isset($_GET['mode']) && $_GET['mode'] == "remove") {

        $id = $_GET['id'];
        $type = strtolower($_GET['type']);
        $object = R::load($type, $_GET['id']);
        R::trash($object);

        header("Location: ../index.php?category=" . $type);

    } else {

        $object = null;

        $type = $_POST['name']['type'];
        $fieldName = $_POST['name']['name'];

        foreach ($user['own'.ucfirst($type)] as $queryObject) {
            if ($queryObject->id == $_POST['pk']) {
                $object = $queryObject;
            }
        }

        $object[$fieldName] = $_POST['value'];
        $object = encryptField($object, $fieldName, $_SESSION['masterHash']);
        R::store($object);

        echo json_encode($object);
    }
} else {
    echo json_encode(new stdClass());
}