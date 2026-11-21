<?php
$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
$file = __DIR__ . $path;
if ( '/' !== $path && is_file( $file ) && ! preg_match( '/\.php$/', $path ) ) { return false; }
if ( is_file( $file ) && preg_match( '/\.php$/', $path ) ) { chdir( dirname( $file ) ); $_SERVER['SCRIPT_NAME'] = $path; $_SERVER['SCRIPT_FILENAME'] = $file; require $file; return true; }
if ( is_dir( $file ) && is_file( rtrim( $file, '/' ) . '/index.php' ) ) { $_SERVER['SCRIPT_NAME'] = rtrim( $path, '/' ) . '/index.php'; chdir( $file ); require rtrim( $file, '/' ) . '/index.php'; return true; }
$_SERVER['SCRIPT_NAME'] = '/index.php'; require __DIR__ . '/index.php';
