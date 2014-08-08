<?php
$include_paths = array_merge(
    // ] NO NO NO NO,
    // ] includes even root, ex index.php controller loops on root index.php
    // 
	// array(get_include_path()),
    // 
	$include_path
);

set_include_path( implode(':', $include_paths) );