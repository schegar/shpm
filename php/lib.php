<?php
//session_start();

define('PROJECT_ROOT', dirname(__DIR__) . '/');
require_once(PROJECT_ROOT.'php/connection.php');
$_SESSION['lang'] = 'eng';
include_once(PROJECT_ROOT.'php/lang/lang.'.$_SESSION['lang'].'.php');

$objects = ['index', 'login', 'banking', 'pin', 'device', 'license'];

$banking = ['id', 'IBAN', 'number', 'pin', 'securecode', 'password', 'comment'];
$login = ['id', 'icon', 'url', 'name', 'username', 'password', 'question', 'comment'];
$device = ['id', 'category', 'name', 'IP', 'domain', 'port', 'username', 'password', 'devicename', 'comment'];
$pin = ['id', 'name', 'username', 'pin', 'comment'];
$license = ['id', 'name', 'username', 'key', 'comment'];

$categories = [
	'banking' => $banking,
	'login' => $login,
	'device' => $device,
	'pin' => $pin,
	'license' => $license
];

function addAccount($accountData, $user) {	
		
	$account = R::dispense('account');
	$account->import($accountData, 'name,username,password,comment');
	$account->encryptPassword($_SESSION['masterHash']);
	$user->ownAccountList[] = $account;
    R::store($user);
	
}

function encryptModel($object, $masterHash) {
	$properties = $object->getProperties();
	$keys = array_keys($properties);
	$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
	$object->salt = $salt;
	$ignoredProperties = ['id', 'icon', 'name', 'category', 'url', 'port', 'devicename', 'domain'];
	for ($i = 0; $i < sizeof($properties); $i++) {
		if (!in_array($keys[$i], $ignoredProperties)){
			$preSalt = encrypt($properties[$keys[$i]], $masterHash);
			$object->{$keys[$i]} = encrypt($preSalt, $salt);
		}
	}
	return $object;
}

function encryptField($object, $field, $masterHash) {
	$ignoredProperties = ['id', 'icon', 'name', 'category', 'url', 'port', 'devicename', 'domain'];
	var_dump($field);
	if (!in_array($field, $ignoredProperties)) {
		$preSalt = encrypt($object[$field], $masterHash);
		$object->{$field} = encrypt($preSalt, $object->salt);
	}
	return $object;
}

function decryptModel($object, $masterHash) {
	$properties = $object->getProperties();
	$keys = array_keys($properties);
	$ignoredProperties = ['id', 'name', 'user', 'category', 'url', 'port', 'user_id', 'salt', 'devicename', 'domain'];
	for ($i = 0; $i < sizeof($properties); $i++) {
		if (!in_array($keys[$i], $ignoredProperties)){
			$preHash = decrypt($properties[$keys[$i]], $object->salt);
			$object->{$keys[$i]} = htmlspecialchars(decrypt($preHash, $masterHash));
		}
	}
	return $object;
}

function getString($key) {
	global $lang;
	return $lang[$key];
}

function encrypt($text, $salt) {
	return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
}

function decrypt($text, $salt) {
	return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
}

function getSitePart($part) {
	echo file_get_contents(PROJECT_ROOT . "php/htmlincludes/" .$part. ".html");
}

function generateAddModal($name) {
	global $categories;
	echo '<div class="modal fade add-modal" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="addModalLabel">Add '.$name.'</h4>
					</div>
					<div class="modal-body">';
						echo '<form id="add' . $name . '">
							<div>';
							foreach($categories[$name] as $part) {
								if (strcmp($part,"password") == 0) {
									echo '<div id="pwd-container">
											<div class="form-group">
												<label for="password" class="control-label">Password</label>
												<div class="input-group">
													<input type="text" class="form-control" id="password">
													<span class="input-group-btn">
													  <button class="btn btn-default" type="button" onclick="generate()">Generate Password</button>
													</span>
												</div>
											</div>
											<div class="form-group">
												<div class="pwstrength_viewport_progress"></div>
											</div>
										</div>';
									;
								} else if (strcmp($part,"category") == 0) {
									echo '<div>
											<div class="form-group">
												<label for="'. $part .'" class="control-label">'. ucfirst($part) .'</label>
												<select class="form-control" name="'. $part .'" id="'. $part .'">';
												$categories = R::getAll( 'SELECT * FROM category' );
												foreach($categories as $category) {
													echo '<option value='.$category['id'].'>'.$category['name'].'</option>';
												}
												echo '</select>';
												echo '
											</div>
										  </div>';
								} else if (strcmp($part, "id") !== 0 && strcmp($part, "icon") !== 0) {
									echo '<div>
											<div class="form-group">
												<label for="'. $part .'" class="control-label">'. ucfirst($part) .'</label>
												<input type="text" class="form-control" name="'. $part .'" id="'. $part .'">
											</div>
										  </div>';
								}
							}
						echo
							'</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="button" class="btn btn-success" data-dismiss="modal" id="submit'.$name.'">Add</button>
					</div>
				</div>
			</div>
		</div>';

	echo '<script type="text/javascript">

		var options = {};
        "use strict";
        options.ui = {
            showPopover: true,
            showErrors: true,
            container: "#pwd-container",
            showVerdictsInsideProgressBar: true,
            viewports: {
                progress: ".pwstrength_viewport_progress"
            },
            progressBarExtraCssClasses: "progress-bar-striped active"
        };
        options.rules = {
            activated: {
                wordTwoCharacterClasses: true,
                wordRepetitions: true
            }
        };
        $("#password").pwstrength(options);
        
        jQuery(function(){
        	$("#submit'.$name.'").click(function() {
        		var data = {}
        		$("#add' . $name . '").find("select").each(function(input) {
        	    	console.log($(this).val());
        	    	data[$(this).attr(\'id\')] = $(this).val(); 
        	    });
        	    $("#add' . $name . '").find("input").each(function(input) {
        	    	console.log($(this).val());
        	    	data[$(this).attr(\'id\')] = $(this).val(); 
        	    });
        	    data["type"] = "'.$name.'";
                $.ajax({
                    method: "POST",
                    url: "php/datahandler.php",
                    data: data,
                    success: function($data){
                        $(":input", "#addAccount").val("");
                        $("#password").pwstrength("forceUpdate");
                        table.ajax.reload();
                    }
                });
            });
        });

		</script>';
}

function getSidebar($current) {
	global $objects;
	echo '<div class="col-sm-3 col-md-2 sidebar">
			<ul class="nav nav-sidebar">';
				foreach($objects as $object) {
					if (strcmp($object,$current) == 0) {
						echo '<li class="active"><a href="#">'.getString($object).'<span class="sr-only">(current)</span ></a></li>';
					} else {
						echo '<li><a href="index.php?category=' .$object.'">'.getString($object).'<span class="sr-only" ></span ></a></li>';
					}
				}
	echo 	'</ul>
				<div class="version">
				v1.0.1 | Ⓒ schegar
				</div>
			</div>';
}

function getNav($current) {
	global $objects;
	echo '<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="index.php?category=index">Self Hosted Password Manager</a>
				</div>
				<div id="navbar" class="navbar-collapse collapse">
					<div class="visible-xs-block">
						<ul class="nav navbar-nav navbar-right">';
							foreach($objects as $object) {
								if (strcmp($object,$current) == 0) {
									echo '<li class="active"><a href="#">'.getString($object).'<span class="sr-only">(current)</span ></a></li>';
								} else {
									echo '<li><a href="index.php?category=' .$object.'">'.getString($object).'<span class="sr-only" ></span ></a></li>';
								}
							}
						echo '</ul><hr>
					</div>
					<ul class="nav navbar-nav navbar-right">
						<li><a href="#" type="button" data-toggle="modal" data-target=".import-account-modal">Import</a></li>
						<li><a href="#" type="button" data-toggle="modal" data-target=".category-modal">Add Category</a></li>
						<li><a href="logout.php">Logout</a></li>
					</ul>
				</div>
			</div>
		</nav>';
}

function getPageHeader($page) {
	echo '<h1 class="page-header">'.getString($page).'
                <div class="pull-right">
                    <button type="button" class="btn btn-success btn-large" data-toggle="modal" data-target=".add-modal">Add</button>
                </div>
          </h1>';
}

function generateTable($type) {
	global $categories;
	echo '<div class="table-responsive">
                <table class="table table-striped table-bordered display responsive" id="accountTable" cellspacing="0" width="100%">
                    <thead>
                    <tr>';
					foreach($categories[$type] as $column){
						if (strpos($column, "id") !== false) {
							echo '<th class="hidden">' . ucfirst($column) . '</th>';
						} else if (strpos($column, "icon") !== false) {
							echo '<th class="nosort">' . ucfirst($column) . '</th>';
						} else if (strpos($column, "category") !== false) {
							echo '<th class="center">' . ucfirst($column) . '</th>';
						} else {
							echo '<th class="export">' . ucfirst($column) . '</th>';
						}
					}
					echo '<th class="nosort">Settings</th>';
                    echo '</tr>
                    </thead>
                </table>
            </div>';

	echo '<script type="text/javascript">

		var data = [];';

		foreach($categories[$type] as $column){
			if (strpos($column, "icon") !== false || strpos($column, "category") !== false) {
				echo 'data.push({data: "' . strtolower($column) . '", width: "1%"}); ';
			} else {
				echo 'data.push({data: "' . strtolower($column) . '"}); ';
			}
		}

		echo 'data.push({data: "settings"});';

		echo ' jQuery(function(){
		
			table = $("#accountTable").DataTable( {
                dom: \'<"floatLeft"B>frt<"floatLeft"i>p\',
                ajax: {
                    "url": "php/datahandler.php?category=' .$type. '",
                    "dataSrc": ""
                },
                responsive: true,
                columns: data,
                columnDefs: [
                	{ targets: "center", sClass: "centerText" },
                    { targets: "hidden", visible: false, searchable: false },
                    { targets: "nosort", orderable: false, searchable: false }
                ],
                fnDrawCallback: function(oSettings) {
                    makeEditable(table);
                },
                buttons: [
                    {
                        extend: "excel",
                        title: "SHPM - '.$type.' ",
                        exportOptions: {
                            columns: ".export"
                        }
                    },
                    {
                        extend: "pdf",
                        title: "SHPM - '.$type.' ",
                        exportOptions: {
                        	columns: ".export"
                        }
                    },
                    {
                        extend: "print",
                        title: "SHPM - '.$type.' ",
                        exportOptions: {
                        	columns: ".export"
                        }
                    },
                    \'colvis\'
                    
                ]
            } );

            table.on( "responsive-display", function ( e, datatable, row, showHide, update ) {
                if (showHide) {
                    makeEditable(table);
                }
            } );
		
		});
		
		function getOptions(id, name) {
            //noinspection JSAnnotator
            return {
                type: \'text\',
                pk: id,
                url: \'php/datahandler.php\',
                name: {
                	name: name,
                	type: "' .$type. '"
                }
            }
        }
		
		function makeEditable(table) {
            $(".editAccount").unbind("click");
            $(".editAccount").click(function (e) {
                e.preventDefault();
                var id = $(this).attr("id");
                var row = null;
                table.rows({ order: "applied" }).every( function ( rowIdx, tableLoop, rowLoop ) {
                    var data = this.data();
                    if (id === data["id"]) {
                        row = ++rowLoop;
                    }
                });
                var index = 0;
                var ignoredAttributes = ["id", "Settings"];
                var editParams = [];
                $("#accountTable tr").eq(row).find("td").each(function (td) {
                	var idx = table.cell($(this)).index().column;
                	var title = table.column(idx).header();
                	var header = $(title).html();
                    if (ignoredAttributes.indexOf(header) === -1) {
                        if (!isInArray(row, edit)) {
                			if (header.toLowerCase() === "category") {
                				var categoryName = $("img", this).attr("alt");
								$(this).wrapInner(\'<a href="#" name="\'+categoryName+\'" data-type="select" class="table-\' + header.toLowerCase() + \'"></a>\');
							} else {
	                            $(this).html($(this).text());
	                            editParams.push(header);
	                            $(this).wrapInner(\'<a href="#" class="table-\' + header.toLowerCase() + \'"></a>\');
                			}
                        } else {
                            var copyString = "<div class=\"copy\" data-clipboard-text=" + $(this).text() + ">" + $(this).text() + "</div>";
                            $(this).html(copyString);
                        }
                    }
                    index++;
                });
                
	            if (!isInArray(row, edit)) {      
	                editParams.forEach(function(name) {
	                	name = name.toLowerCase();
	                	$(".table-" + name).editable(getOptions(id, name));                
	                });
                    if ("'.$type.'" === "device") {
                        var name = $(".table-category").attr("name");
                        $(".table-category").editable({
                            value: name.substr(0, 1),
                            source: "php/datahandler.php?request=categories",
                            sourceCache: true,
                            type: \'text\',
                            pk: id,
                            url: \'php/datahandler.php\',
                            name: {
                                name: "category",
                                type: "' .$type. '"
                            }
                        });
                    }
                              
                	edit.push(row);
                } else {
                    edit.splice(edit.indexOf(row), 1);
                    table.ajax.reload();
                }
            });
        }

		</script>';
}

function parseUrl($object) {
	$iconLink = "";
	$host = "";

	if (strpos($object->url, "http") !== false) {
		$host = parse_url($object->url)['host'];
		$iconLink = 'http://www.google.com/s2/favicons?domain=' . $host;
		$object->url = '<a href="' . $object->url . '" target="_blank">' . $object->url . '</a>';
	} else {
		$host = "default";
		$iconLink = 'http://tiny.cc/public/images/default-favicon.ico';
		$object->url = '<div class="copy" data-clipboard-text="' . $object->url . '">' . $object->url . '</div>';
	}

	if (!file_exists("../icons/" . $host . ".ico")) {
		file_put_contents("../icons/" . $host . ".ico", file_get_contents($iconLink));
	}

	$object->icon = '<img src="' . "icons/" . $host . ".ico" . '" alt="' . $iconLink . '">';

	return $object;
}

function addCategoryIcon($object) {

	$categoryId = $object->category;
	$category = R::load('category', $categoryId);
	if (file_exists("../icons/" . strtolower($category->name) . ".ico")) {		
		$object->category = '<img src="' . "icons/" . strtolower($category->name) . ".ico" . '" alt="' . $categoryId . $category->name . '">';
	} else {
		$object->category = $category->name;
	}
	
	return $object;
}
