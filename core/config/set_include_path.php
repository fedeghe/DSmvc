<?php
$include_paths = array_merge(
	array(get_include_path()),
	$include_path
);

set_include_path( implode(':', $include_paths) );