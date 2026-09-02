<?php
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

// Session length : 3 hours
const SESSION_LENGTH = 3600*3;

// 1. Make garbage collector not working

// Garbage collector cleanup probability = gc_probability/gc_divisor
ini_set('session.gc_probability', 0); // so it never run!
ini_set('session.gc_divisor', 100);
ini_set('session.gc_maxlifetime', SESSION_LENGTH);

// 2. Implement our own "garbage collector"
session_start();

$now = time();

// Check if session expired
if (isset($_SESSION['session_expiration']) && $now > $_SESSION['session_expiration']) {
    // If so destroy and restart
    session_unset();
    session_destroy();
    session_start();
}

// Calculate expiration
$_SESSION['session_expiration'] = $now + SESSION_LENGTH;

$session = new Session(new PhpBridgeSessionStorage());
$session->start();
