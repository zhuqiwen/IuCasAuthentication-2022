<?php

header('Content-Type: text/xml; charset=utf-8');

$xmlSuccess = <<< SUCCESS
<?xml version="1.0" encoding="UTF-8"?>
<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
	<cas:authenticationSuccess>
		<cas:user>username</cas:user>
	</cas:authenticationSuccess>
</cas:serviceResponse>
SUCCESS;


$xmlFail = <<< FAIL
<?xml version="1.0" encoding="UTF-8"?>
<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
	<cas:authenticationFailure code='INVALID_TICKET'>
		E_TICKET_FAIL
	</cas:authenticationFailure>
</cas:serviceResponse>
FAIL;

if(preg_match('/^\/serviceValidate\/yes\\?/', $_SERVER["REQUEST_URI"]) && isset($_GET['ticket'])){
    echo $xmlSuccess;
}
else{
    echo $xmlFail;
}



