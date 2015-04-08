<?php
/*
* Edit Hopper plugin uninstaller.
*/

if(!defined('WP_UNINSTALL_PLUGIN'))
	exit;

delete_option('eh-enabled');
?>