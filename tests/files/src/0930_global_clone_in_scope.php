<?php

$myGlobal = 'foo';

function alterMyGlobal() {
    global $myGlobal;
    $myGlobal = 'definitely not foo';
    '@phan-debug-var $myGlobal';//Should infer real type 'definitely not foo' here.
}

alterMyGlobal();
'@phan-debug-var $myGlobal';//Should have no real type.

function maybeAlterMyGlobal() {
    global $myGlobal;
    if ( rand() ) {
        $myGlobal = 'not guaranteed';
        '@phan-debug-var $myGlobal';//Should infer real type 'not guaranteed' here.
    }
    '@phan-debug-var $myGlobal';//Should not have a real type
}

maybeAlterMyGlobal();
'@phan-debug-var $myGlobal';//Should not have a real type



function setSecondGlobal() {
    global $secondGlobal;
    $secondGlobal = 'foo';
    '@phan-debug-var $secondGlobal';// Real type 'foo'
}
setSecondGlobal();

function alterSecondGlobal() {
	global $secondGlobal;
	$secondGlobal = 'definitely not foo';
    '@phan-debug-var $secondGlobal';// Real type 'definitely not foo'
}
alterSecondGlobal();

'@phan-debug-var $secondGlobal';//No real type
