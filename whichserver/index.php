<?
	echo $_GET['host'] . '<br/>';
	$ip = gethostbyname($_GET['host']); 
	if ($ip != $_GET['host']) {
		echo 'IP: ' . $ip . '<br/>';
		$server = gethostbyaddr($ip);
		echo 'Server: ' . $server;
	} else {
		echo 'DNS lookup failed.';
	}