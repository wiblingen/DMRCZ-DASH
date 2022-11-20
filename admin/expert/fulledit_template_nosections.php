<?php

if (!isset($_SESSION) || !is_array($_SESSION)) {
    session_id('pistardashsess');
    session_start();
    
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code
    checkSessionValidity();
}

// Load the language support
require_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
    <head>
	<meta name="robots" content="index" />
	<meta name="robots" content="follow" />
	<meta name="language" content="English" />
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="pragma" content="no-cache" />
	<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />
	<meta http-equiv="Expires" content="0" />
	<title>Pi-Star - Digital Voice Dashboard - Expert Editor</title>
	<link rel="stylesheet" type="text/css" href="/css/font-awesome-4.7.0/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="/css/pistar-css.php?version=<?php echo $versionCmd; ?>" />
    </head>
    <body>
	<div class="container">
	    <?php include './header-menu.inc'; ?>
	    <div class="contentwide">
		<?php
		if(isset($_POST['data'])) {
		    // split and remove DOS EOL
		    $data = explode("\n", str_replace("\r", "", $_POST['data']));
		    
		    // Prepare data and update the configuration file
		    $content = "";
		    foreach($data as $line) {
			if (strpos($line, '=') !== FALSE) {
			    list($key, $value) = explode('=', $line, 2);
			    $value = trim(str_replace('"', '', $value));
			    
 			    if (isset($key) && !empty($key)) {
				    if (function_exists('process_before_saving')) {
					process_before_saving($key, $value);
				    }
		  
				if ($value == '') {
				    $content .= $key."= \n";
				}
				else {
				    $content .= $key."=".$value."\n";
				}
			    }
			}
		    }

		    $filepath = $tempfile;
		    $wCount = FALSE;
		    // write it into file
		    if (($handle = fopen($tempfile, 'w')) != FALSE) {
			if (($wCount = fwrite($handle, $content)) != FALSE) {
			    // Updates complete - copy the working file back to the proper location
			    exec('sudo mount -o remount,rw /');
			    exec('sudo cp '.$tempfile.' '.$configfile.'');
			    exec('sudo chmod 644 '.$configfile.'');
			    exec('sudo chown root:root '.$configfile.'');
			    exec('sudo mount -o remount,ro /');
			    
			    // Reload the affected daemon
			    if (isset($servicenames) && (count($servicenames) > 0)) {
				foreach($servicenames as $servicename) {
				    exec('sudo systemctl restart '.$servicename); // Reload the daemon
				}
			    }
			}
			
			fclose($handle);
		    }
		    
		    // Re-open the file and read it
		    $fh = fopen($filepath, 'r');
		    $theData = fread($fh, filesize($filepath));
		}
		else {
		    // File Wrangling
		    exec('sudo cp '.$configfile.' '.$tempfile);
		    exec('sudo chown www-data:www-data '.$tempfile);
		    exec('sudo chmod 664 '.$tempfile);
		    
		    // Open the file and read it
		    $filepath = $tempfile;
		    $fh = fopen($filepath, 'r');
		    $theData = fread($fh, filesize($filepath));
		}
		fclose($fh);
		
		?>
		
		<form name="test" method="post" action="">
		    <label for="data" class="header" style="display:block;text-align:center;" ><?php echo $editorname ?></label> 
		    <textarea id="data" name="data" cols="80" rows="45"><?php echo $theData; ?></textarea><br />
		    <input type="submit" name="submit" value="<?php echo $lang['apply']; ?>" />
		</form>
		
	    </div>
	    
	    <div class="footer">
		Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
		<a href="https://w0chp.net/w0chp-pistar-dash/" style="color: #ffffff; text-decoration:underline;">W0CHP-PiStar-Dash</a> enhancements by W0CHP<br />
	    </div>
	    
	</div>
    </body>
</html>
