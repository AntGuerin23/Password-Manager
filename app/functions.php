<?php

/**
 * Includes any global functions needed for your project. Ideally, you should follow a more OOP approach using object
 * instead of functions, but it can become handy for defining quick function available through Pug files or any PHP
 * files. Normally used to mimic very low level operation akin to a native PHP function.
 */

function getImagePath($name) {
    switch (true) {
        case stristr($name, "Netflix") :
            return "netflix.jpg";
        case stristr($name, "Facebook") :
            return "facebook.png";
        case stristr($name, "Google") :
            return "google.png";
        case stristr($name, "Github") :
            return "github.png";
        case stristr($name, "Spotify") :
            return "spotify.png";
        default :
            return "lock.png";
    }
}