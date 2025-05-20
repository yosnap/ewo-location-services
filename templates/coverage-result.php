<?php
// Template for displaying coverage result
// Expects: $status (string)
if (!isset($status)) return;

switch (strtolower($status)) {
    case 'yes':
        echo '<h2>Great news! We have coverage in your area.</h2>';
        echo '<p>You can proceed to select your service plan.</p>';
        break;
    case 'maybe':
        echo '<h2>Coverage might be available.</h2>';
        echo '<p>Please contact our support for more details.</p>';
        break;
    case 'not yet':
        echo '<h2>Coverage is not available yet.</h2>';
        echo '<p>Leave your details and we will notify you when it becomes available.</p>';
        break;
    case 'no':
        echo '<h2>Sorry, there is no coverage at your location.</h2>';
        echo '<p>Check back soon or contact us for alternatives.</p>';
        break;
    default:
        echo '<h2>Coverage status unknown.</h2>';
        break;
} 