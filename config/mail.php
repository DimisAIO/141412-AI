<?php
/*
	Mail configuration
	
	This file will help you config account verification by link
	
	$mailEnabled:
		True - enable mail verification
		False - disable mail verification

	$mailbox - Your mail server
	$mailport - Port of your mail server
	$mailuser - Your mail account's login
	$mailpass - Your mail account's password
	$mailtype:
		'tls' - Use TLS encryption for sending mails
		'ssl' - Use SSL encryption for sending mails
		false - Don't encrypt mails
	$yourmail - This is what will player see as mail sender, often should be same as $mailuser
	
	Note: you can use your Gmail or other mail services for this, though you need to find tutorial yourself
    (mega thx 4 mail code)
*/
$mailEnabled = false;

$mailbox = 'mail.provider.com';
$mailport = 587;
$mailuser = 'user';
$mailpass = 'password';
$mailtype = 'tls';
$yourmail = 'noreply@service.com';
?>