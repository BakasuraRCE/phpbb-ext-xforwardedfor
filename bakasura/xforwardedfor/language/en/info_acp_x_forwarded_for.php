<?php

if (!defined('IN_PHPBB')) {
	exit;
}

if (empty($lang) || !is_array($lang)) {
	$lang = [];
}

$lang = array_merge($lang, [
	'XFF_ACP_TITLE'                  => 'X-Forwarded-For',
	'XFF_ACP'                        => 'Settings',
	'XFF_ACP_CLOUDFLARE_MODE'        => 'Cloudflare mode',
	'XFF_ACP_CLOUDFLARE_MODE_EXPLAIN' => 'Enable this if your site is behind Cloudflare. When enabled, the real client IP is read exclusively from the <code>CF-Connecting-IP</code> header and the trusted IP list below is ignored.',
	'XFF_ACP_TRUSTED_IPS'            => 'Comma-separated list of trusted proxy IPs allowed to forward the X-Forwarded-For header',
	'XFF_ACP_SETTING_SAVED'          => 'Settings have been saved successfully.',
]);
