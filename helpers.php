<?php
// helpers.php – modern ersättning för fmview.php

function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

function formatTime($time, $format = 'H:i:s') {
    return date($format, strtotime($time));
}

function formatTimestamp($timestamp, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($timestamp));
}
?>