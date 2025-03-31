<?php
// Sitewide AI name
$aiName = "ChatBox";

/*
	Obtaining IP:
    	$_SERVER["REMOTE_ADDR"] => The most common way of getting the IP of the client
        $_SERVER["HTTP_X_FORWARDED_FOR"] => Used in multiple proxy cases, such as Apache behind a proxy!
        $_SERVER["HTTP_CF_CONNECTING_IP"] => When using cloudflare!
*/
$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];

// Email for banned users to appeal/contact to :
$adminmail = "admin@provider.com";

// Lockfile (If an AI instance is ran, you cannot run another)
$useLockFile = true;

// Captcha Settings:
$enableCaptcha = false;
$captchaType = 2; // 1 => reCaptcha, 2 => HCaptcha, 3 => CF Turnstile
$captchaKey = "key";
$captchaSecret = "secret";