<?
	if (!strlen($_GET['query'])) { die; }

	if (!isset($_SERVER['PHP_AUTH_USER'])) {
		header("WWW-Authenticate: Basic realm=\"admintools\"");
		header("HTTP/1.0 401 Unauthorized");
		exit();
	} else {
		if (!mysql_connect('localhost',$_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])) {
			header("WWW-Authenticate: Basic realm=\"admintools\"");
			header("HTTP/1.0 401 Unauthorized");
			exit();
		}
	}
	
	mysql_select_db('psa');
	
	$i_dbType = 0;
	
	$result = mysql_query("SELECT * FROM db_users") or die('Invalid \'psa\' database.');
	$row = mysql_fetch_assoc($result);
	if (array_key_exists('account_id',$row)) { $i_dbType = 1; }
	
	$queries = array(
		1 =>
		"SELECT 
		d.name,dbu.login AS db_login,a.password AS db_password
		FROM domains d
		INNER JOIN data_bases db ON d.id=db.dom_id
		LEFT OUTER JOIN db_users dbu ON db.id=dbu.db_id
		LEFT OUTER JOIN accounts a ON dbu." . ($i_dbType ? 'account_' : '') . "id=a.id
		WHERE 
		d.name like \"%" . mysql_real_escape_string($_GET["query"]). "%\" OR
		dbu.login like \"%" . mysql_real_escape_string($_GET["query"]). "%\" OR
		a.password like \"%" . mysql_real_escape_string($_GET["query"]). "%\"
		ORDER BY d.name,db_login",

		2 =>
		"SELECT 
		su.login AS system_login, a1.password AS system_password, home AS system_home
		FROM sys_users su 
		LEFT OUTER JOIN accounts a1 ON su.account_id=a1.id
		WHERE
		su.login like \"%" . mysql_real_escape_string($_GET["query"]). "%\" OR
		a1.password like \"%" . mysql_real_escape_string($_GET["query"]). "%\" OR
		home like \"%" . mysql_real_escape_string($_GET["query"]). "%\"
		ORDER BY su.login",

		3 =>
		"SELECT 
		d.name,m.mail_name, a.password AS mail_password
		FROM domains d
		INNER JOIN mail m ON d.id=m.dom_id
		LEFT OUTER JOIN accounts a ON m.account_id=a.id
		WHERE
		d.name like \"%" . mysql_real_escape_string($_GET["query"]). "%\" OR
		m.mail_name like \"%" . mysql_real_escape_string($_GET["query"]). "%\" OR
		a.password like \"%" . mysql_real_escape_string($_GET["query"]). "%\"
		ORDER BY d.name,m.mail_name",

	 	4 =>
		"SELECT 
		c.cname, c.email, c.login, a.password AS client_password
		FROM clients c
		LEFT OUTER JOIN accounts a ON c." . ($i_dbType ? 'account_' : '') . "id=a.id
		WHERE
		c.cname like \"%" . mysql_real_escape_string($_GET["query"]). "%\" OR
		c.email like \"%" . mysql_real_escape_string($_GET["query"]). "%\" OR
		c.login like \"%" . mysql_real_escape_string($_GET["query"]). "%\" OR
		a.password like \"%" . mysql_real_escape_string($_GET["query"]). "%\"
		ORDER BY c.cname,c.email",
	
		5 =>
		"SELECT
		d.name,pd.path, pdu.login, a.password
		FROM domains d
		INNER JOIN protected_dirs pd ON d.id=pd.dom_id
		LEFT OUTER JOIN pd_users pdu ON pd.id=pdu.pd_id
		LEFT OUTER JOIN accounts a ON pdu.account_id=a.id
		WHERE
		d.name like \"%" . mysql_real_escape_string($_GET["query"]). "%\" OR
		pd.path like \"%" . mysql_real_escape_string($_GET["query"]). "%\" OR
		pdu.login like \"%" . mysql_real_escape_string($_GET["query"]). "%\" OR
		a.password like \"%" . mysql_real_escape_string($_GET["query"]). "%\" 
		ORDER BY d.name,pdu.login",

 		6 =>
		"SELECT d.name, a.password
		FROM domains d INNER JOIN dom_level_usrs du ON d.id=du.dom_id 
		INNER JOIN accounts a ON du." . ($i_dbType ? 'account_' : '') . "id=a.id
		WHERE
		d.name like \"%" . mysql_real_escape_string($_GET["query"]). "%\""
	);
	
	$headings = array(
		1 => "Domain - Databases",
		2 => "System Users",
		3 => "Domain - Mailboxes",
		4 => "Clients",
		5 => "Protected Directories",
		6 => "Domains - Admins"
	);
	
	foreach ($queries as $id => $query) {
		
		$result = mysql_query($query) or die("Query failed: " . mysql_error());
		
		echo "<p style=\"margin-bottom: 0;\"><strong>" . $headings[$id] . "</strong></p>";
		
		echo "<table cellpadding='6''>\n";
		
		$fieldcount = mysql_num_fields($result);
		for($i = 0; $i < $fieldcount; $i++) {
			echo "<th bgcolor='#f6f6f6' style='color:#cccccc;'>" . mysql_field_name($result,$i) . "</th>";
		}
		
		$rowcount = 0;
		while ($row = mysql_fetch_assoc($result)) {
			
			$rowcount++;
			$bgcolour = ($rowcount % 2 ? '#e1e1e1' : '#f1f1f1');
			
			echo "\t<tr>\n";
			
			foreach ($row as $value) {
				
				echo "\t\t<td bgcolor=\"$bgcolour\">$value</td>\n";
			}
			
			echo "\t</tr>\n";
		}
		
		echo "</table>\n";
	}