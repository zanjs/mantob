<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

$active_group	= 'default';
$query_builder	= TRUE;

$db['default']	= array(
	'dsn'		=> '',
	'hostname'	=> '127.0.0.1',
	'username'	=> 'root',
	'password'	=> '123456',
	'port'		=> '3306',
	'database'	=> 'man_luo',
	'dbdriver'	=> 'mysql',
	'dbprefix'	=> 'man_',
	'pconnect'	=> FALSE,
	'db_debug'	=> TRUE,
	'cache_on'	=> FALSE,
	'cachedir'	=> 'cache/sql/',
	'char_set'	=> 'utf8',
	'dbcollat'	=> 'utf8_general_ci',
	'swap_pre'	=> '',
	'autoinit'	=> FALSE,
	'encrypt'	=> FALSE,
	'compress'	=> FALSE,
	'stricton'	=> FALSE,
	'failover'	=> array(),
);
