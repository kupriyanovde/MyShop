<?php
// Site
$_['site_url']           = 'http://shop.ru/';

// Database
$_['db_engine']          = 'pdo';  
$_['db_driver']          = 'mysql'; // mysqli, pdo or pgsql
$_['db_hostname']        = 'localhost';
$_['db_username']        = 'username';
$_['db_password']        = 'password';
$_['db_database']        = 'u1169167_shop';
$_['db_port']            = '3306';
$_['db_ssl_key']         = '';
$_['db_ssl_cert']        = '';
$_['db_ssl_ca']          = '';

// Session
$_['session_autostart']  = false;
$_['session_engine']     = 'db'; // db or file

// Actions
$_['action_pre_action']  = [
	'startup/setting',
	'startup/seo_url',
	'startup/session',
	'startup/language',
	'startup/customer',
	'startup/currency',
	'startup/tax',
	'startup/application',
	'startup/extension',
	'startup/startup',
	'startup/marketing',
	'startup/error',
	'startup/event',
	'startup/sass',
	'startup/api',
	'startup/maintenance'
];

// Action Events
$_['action_event']      = [
	'controller/*/before' => [
		0 => 'event/modification.controller',
		1 => 'event/language.before',
		//2 => 'event/debug.before'
	],
	'controller/*/after' => [
		0 => 'event/language.after',
		//2 => 'event/debug.after'
	],
	'view/*/before' => [
		0   => 'event/modification.view',
		500 => 'event/theme',
		998 => 'event/language'
	],
	'language/*/before' => [
		0 => 'event/modification.language'
	],
	'language/*/after' => [
		0 => 'startup/language.after',
		1 => 'event/translation'
	]
];

return $_;
