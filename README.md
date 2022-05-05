# IULoginCAS2

First, the majority of credits go to [Tom Gregory](https://kelley.iu.edu/faculty-research/faculty-directory/profile.html?id=TOMGREG) (@tomgreg) and [Lee Hadley](https://studios.iu.edu/about/people/hadley-lee.html) (@leehadle). This lib is based on their work.

Implementing a script with [IU Login 2.0 with CAS](https://kb.iu.edu/d/bfpq) is not hard. Hopefully this lib will make it even easier.

Note that this lib only authenticate if user has a valid iu credential. It DOES NOT come with any authorization, or permission, feature. 

For your app to have finer granularity for access control, consider implementing a role-based access control system.  

**Tom Gregory's original repo** for previous CAS login: https://github.com/tag/IuCasAuthentication

**Lee Hadley's work** of a working script for current CAS login:
```php
<?php
# modified 1/26/22 to work with IU Login by Lee Hadley leehadley@iu.edu 
# please don't blame me for the original or the form app itself 
# it's better than when I found it :) 

session_save_path('/groups/office/sessions'); //UPDATE TO YOUR SESSIONS PATH
session_start();
 
 
//THIS FUNCTION GETS THE CURRENT URL
function curPageURL(){
  $pageURL = 'http';
  if ($_SERVER["HTTPS"] == "on") {
    $pageURL .= "s://";
    if ($_SERVER["SERVER_PORT"] != "443") {
      $pageURL .= $_SERVER["HTTP_HOST"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
      $pageURL .= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
    }
  } else {
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
      $pageURL .= $_SERVER["HTTP_HOST"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
      $pageURL .= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
    }
  }
  return $pageURL;
}//END CURRENT URL FUNCTION
 
 
//THIS FUNCTION SENDS THE USER TO CAS AND THEN BACK
function cas_authenticate(){
 
  $sid = SID; //Session ID #
	
	if(!isset($_SESSION['CAS'])){
		$_SESSION['CAS'] = false;
	}
 
  //if the last session was over 15 minutes ago
  if (isset($_SESSION['LAST_SESSION']) && (time() - $_SESSION['LAST_SESSION'] > 900)) {
    $_SESSION['CAS'] = false; // set the CAS session to false
  }
 
  $authenticated = $_SESSION['CAS'];
  $casurl = curPageURL();

	$iu_login = 'https://idp.login.iu.edu';
	if(substr_count($casurl, 'sitehost-test')){
		$iu_login = 'https://idp-stg.login.iu.edu';
	}
	
 
  //send user to CAS login if not authenticated
  if (!$authenticated) {
    $_SESSION['LAST_SESSION'] = time(); // update last activity time stamp
    $_SESSION['CAS'] = true;
    echo '<META HTTP-EQUIV="Refresh" Content="0; URL=' . $iu_login . '/idp/profile/cas/login?service='.$casurl.'">';
    exit;
  }
	

  if ($authenticated) {
    if (isset($_GET["ticket"])) {
      //set up validation URL to ask CAS if ticket is good
			
			$casurl = str_replace('?ticket='.$_GET['ticket'], '', $casurl);
			// validate the ticket
			$validate = $iu_login . '/idp/profile/cas/serviceValidate?ticket=' . $_GET['ticket'] . '&service=' . $casurl;
 
      // Set up curl, and tell it to fetch the cas ticket from the cas server specified
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $validate);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$results = curl_exec($curl);
			
			// if the user is authenticated
			if (substr_count($results, 'authenticationSuccess')) {
					// set a session variable for the user
					$user = substr($results, strpos($results, '<cas:user>') + 10);
					$user = substr($user, 0, strpos($user, '</cas:user>'));

					$_SESSION['user'] = $user;
			} 
  	} else if (!isset($_SESSION['user'])) { //END GET CAS TICKET
				echo '<META HTTP-EQUIV="Refresh" Content="0; URL=' . $iu_login . '/idp/profile/cas/login?service='.$casurl.'">';
		}
	}
}//END CAS FUNCTION
 
cas_authenticate();

//gets the username from the SESSION variable 'user' created by CAS
$username = $_SESSION['user'];
 
//CHANGE THIS LIST TO THE USERS YOU'D LIKE TO HAVE ACCESS
//$users = array("user1", "user2", "user3");
//if(!in_array($username, $users)){
//  die("Sorry you do not have access to this page.");
//}
 
//UNCOMMENT NEXT 3 LINES IF YOU'D LIKE TO RESTRICT TO A SINGLE USER
//if($username != "user"){
//  die("Sorry you do not have access to this page.");
//}
 
?>
```

## Installation
2 ways: using [Composer](https://getcomposer.org/), or direct `include`/`require`

### Composer
run the following in your composer project's root folder, where the `composer.json` resides.
```shell
composer require iu-vpcm/cas2
```
### plain php include/require
Download the script, name it as you wish (for example `cas2.php`) and in your scripts:
```php
require 'PATH-TO/cas2.php';
// or 
// inlcude 'PATH-TO/cas2.php'
```
## Usage
```php
$cas = new IULoginCAS2();
```

to authenticate:
```php
$cas->authenticate();
```

to get username after authenticate:
```php
$username = $cas->getUsername();
```