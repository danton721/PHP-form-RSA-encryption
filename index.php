<?php

function decrypt($field){
	// Load private key into PHP
	$private = "-----BEGIN RSA PRIVATE KEY-----
MIICXwIBAAKBgQCiNb2DQUhlTHnaQeBFkw9vJZ+JWluH7xf1TBjHB5U/fLMa05Gk
aHYW4YEzlGU3ww1yYjLXS8vEbGV7QYKYRj71kxukgmsdDjmqHGxZBo5F8i+hSI1Y
VFcJEZluf3wMq94hOLM0eF/GUEu8htvHsRyng4z68NFKWsidMUgSxnyyRQIDAQAB
AoGBAJ9krW/f45Le/lIRL59ObfkrAETJDG5b7K/28dYJxofXMmwm/9ONbpT3TK1x
obCUs471nb3f1kCSv3nJmtmlFVFCyRvcWHaDSIWdW78Qwhh2ra63BrFvAd5/hjX+
Lj5eOXpQmGeDdLg6O8pintvQRefAkF/Lfsux4QexEH7JcaYVAkEA/i7WQ4wNGj28
aEraH+9b4g8pfPPYrUlC3yvLmy4058wg1BlgUjZGPcQdXSsLIBcVxv+6XU2BKG15
/rURxZxuNwJBAKNelvXfhiUtYXFLSRUWIE9I1KtiALaQW6rk914akXx2HHtH1uR8
k1w9/YRYH5CU5UgkGS+y3yIcQlvhRcZhBWMCQQCvdzOwE2kkGUQLlsh2zSxXpHHW
cRq9nNpN5xS5vi8FaNOstwvYFOFuWAiRPVqDv4voALbtG8iyWMijfOmUycUHAkEA
gt24q8icWpeZoPmf12ZcB2beBVOCIrxM0f6MMTOzKzIp6o9Hksw/9vopZKR61ISR
jlJsYos0tsxQU+2GyTza5QJBAPNbWBCZnKEygqf72g5L+BtL99SeL8EpiRjI1RBa
jtAgdfZa0Un1Pn9XKe3cfOjDG35wSZyIj1vZTwB96MlAelE=
-----END RSA PRIVATE KEY-----";
	if (!$privateKey = openssl_pkey_get_private($private)) die('Loading Private Key failed');
	
	// Decrypt text
	$decrypted_text = "";
	if (!openssl_private_decrypt(base64_decode($field), $decrypted_text, $privateKey)) die('Failed to decrypt data');
	
	// Get time from the end of decrypted string
	$strTime = (int)substr($decrypted_text, strripos($decrypted_text, "+")+1);
	$decrypted_text = substr($decrypted_text, 0, strripos($decrypted_text, "+"));
	
	// Get time from server
	$date = new DateTime();
	$time = $date->getTimestamp();

	// Free the key from PHP
	openssl_free_key($privateKey);
	
	// Here you can configure the offset time from server request + JavaScript loading time variables
	// Setted to 30s difference to avoid any problems. Hacker could replay attack only in 30 seconds.
	if(($time - $strTime) < 30){
		//Decrypted
		return $decrypted_text;
	} else {
		return false;
	}
	
}

// If post request...
if (isset($_POST['password'])) {
	
	echo $_POST['password'];
	
	$var = decrypt($_POST['password']);
	
	var_dump($var);
	
}
?>

<html>
	<head>
	    <script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
		<script src="/includes/jsencrypt.min.js"></script>
			<script language="JavaScript">
	
			// Get time since page load.
			var scriptInitTime = Math.round(+new Date()/1000);
			// Receive server time.
			var serverTime = <?php $date = new DateTime(); echo $date->getTimestamp(); ?>
			
			function encrypt(){	
				// Get time since submission.
				var submitTime = Math.round(+new Date()/1000);
				// Calculate time difference since script execution, submission and add to server time
				// This avoids clock differences on user machine and server, leaving only internet speed
				// and JavaScript processing time as variables to fail.
				var timeDiff = serverTime + (submitTime - scriptInitTime);
	
				// Set public key
				var pem = "-----BEGIN PUBLIC KEY-----MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCiNb2DQUhlTHnaQeBFkw9vJZ+JWluH7xf1TBjHB5U/fLMa05GkaHYW4YEzlGU3ww1yYjLXS8vEbGV7QYKYRj71kxukgmsdDjmqHGxZBo5F8i+hSI1YVFcJEZluf3wMq94hOLM0eF/GUEu8htvHsRyng4z68NFKWsidMUgSxnyyRQIDAQAB-----END PUBLIC KEY-----";
				var encrypt = new JSEncrypt();
				encrypt.setPublicKey(pem);
				// Encrypt #password field with server time + difference since execution
				document.getElementById('password').value = encrypt.encrypt($('#password').val() + "+" + timeDiff);
			}
		</script>	
	</head>
	<body>
		<form method="post" action="/index.php" name="registration_form" id='txtAuth'>
			<input type="text" name="password" id="password">
			<input type="submit" value="assemble" onclick="encrypt()"> 
		</form>
	</body>
</html>