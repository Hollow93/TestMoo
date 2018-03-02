<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Private url2 module utility functions
 *
 * @package    mod_url2
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/url2/lib.php");

/**
 * This methods does weak url2 validation, we are looking for major problems only,
 * no strict RFE validation.
 *
 * @param $url2
 * @return bool true is seems valid, false if definitely not valid url2
 */
function url2_appears_valid_url2($url2) {
    if (preg_match('/^(\/|https?:|ftp:)/i', $url2)) {
        // note: this is not exact validation, we look for severely malformed url2s only
        return (bool)preg_match('/^[a-z]+:\/\/([^:@\s]+:[^@\s]+@)?[a-z0-9_\.\-]+(:[0-9]+)?(\/[^#]*)?(#.*)?$/i', $url2);
    } else {
        return (bool)preg_match('/^[a-z]+:\/\/...*$/i', $url2);
    }
}

/**
 * Fix common url2 problems that we want teachers to see fixed
 * the next time they edit the resource.
 *
 * This function does not include any XSS protection.
 *
 * @param string $url2
 * @return string
 */
function url2_fix_submitted_url2($url2) {
    // note: empty url2s are prevented in form validation
    $url2 = trim($url2);

    // remove encoded entities - we want the raw URI here
    $url2 = html_entity_decode($url2, ENT_QUOTES, 'UTF-8');

    if (!preg_match('|^[a-z]+:|i', $url2) and !preg_match('|^/|', $url2)) {
        // invalid URI, try to fix it by making it normal url2,
        // please note relative url2s are not allowed, /xx/yy links are ok
        $url2 = 'http://'.$url2;
    }

    return $url2;
}

/**
 * Return full url2 with all extra parameters
 *
 * This function does not include any XSS protection.
 *
 * @param string $url2
 * @param object $cm
 * @param object $course
 * @param object $config
 * @return string url2 with & encoded as &amp;
 */
function url2_get_full_url2($url2, $cm, $course, $config=null) {

    $parameters = empty($url2->parameters) ? array() : unserialize($url2->parameters);

    // make sure there are no encoded entities, it is ok to do this twice
    $fullurl2 = html_entity_decode($url2->externalurl2, ENT_QUOTES, 'UTF-8');

    if (preg_match('/^(\/|https?:|ftp:)/i', $fullurl2) or preg_match('|^/|', $fullurl2)) {
        // encode extra chars in url2s - this does not make it always valid, but it helps with some UTF-8 problems
        $allowed = "a-zA-Z0-9".preg_quote(';/?:@=&$_.+!*(),-#%', '/');
        $fullurl2 = preg_replace_callback("/[^$allowed]/", 'url2_filter_callback', $fullurl2);
    } else {
        // encode special chars only
        $fullurl2 = str_replace('"', '%22', $fullurl2);
        $fullurl2 = str_replace('\'', '%27', $fullurl2);
        $fullurl2 = str_replace(' ', '%20', $fullurl2);
        $fullurl2 = str_replace('<', '%3C', $fullurl2);
        $fullurl2 = str_replace('>', '%3E', $fullurl2);
    }

    // add variable url2 parameters
    if (!empty($parameters)) {
        if (!$config) {
            $config = get_config('url2');
        }
        $paramvalues = url2_get_variable_values($url2, $cm, $course, $config);

        foreach ($parameters as $parse=>$parameter) {
            if (isset($paramvalues[$parameter])) {
                $parameters[$parse] = rawurl2encode($parse).'='.rawurl2encode($paramvalues[$parameter]);
            } else {
                unset($parameters[$parse]);
            }
        }

        if (!empty($parameters)) {
            if (stripos($fullurl2, 'teamspeak://') === 0) {
                $fullurl2 = $fullurl2.'?'.implode('?', $parameters);
            } else {
                $join = (strpos($fullurl2, '?') === false) ? '?' : '&';
                $fullurl2 = $fullurl2.$join.implode('&', $parameters);
            }
        }
    }

    // encode all & to &amp; entity
    $fullurl2 = str_replace('&', '&amp;', $fullurl2);

    return $fullurl2;
}

/**
 * Unicode encoding helper callback
 * @internal
 * @param array $matches
 * @return string
 */
function url2_filter_callback($matches) {
    return rawurl2encode($matches[0]);
}

/**
 * Print url2 header.
 * @param object $url2
 * @param object $cm
 * @param object $course
 * @return void
 */
function url2_print_header($url2, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$url2->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($url2);
    echo $OUTPUT->header();
}

/**
 * Print url2 heading.
 * @param object $url2
 * @param object $cm
 * @param object $course
 * @param bool $notused This variable is no longer used.
 * @return void
 */
function url2_print_heading($url2, $cm, $course, $notused = false) {
    global $OUTPUT;
    echo $OUTPUT->heading(format_string($url2->name), 2);
}

/**
 * Print url2 introduction.
 * @param object $url2
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function url2_print_intro($url2, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($url2->displayoptions) ? array() : unserialize($url2->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($url2->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'url2intro');
            echo format_module_intro('url2', $url2, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Display url2 frames.
 * @param object $url2
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function url2_display_frame($url2, $cm, $course) {
    global $PAGE, $OUTPUT, $CFG;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        url2_print_header($url2, $cm, $course);
        url2_print_heading($url2, $cm, $course);
        url2_print_intro($url2, $cm, $course);
        echo $OUTPUT->footer();
        die;

    } else {
        $config = get_config('url2');
        $context = context_module::instance($cm->id);
        $exteurl2 = url2_get_full_url2($url2, $cm, $course, $config);
        $navurl2 = "$CFG->wwwroot/mod/url2/view.php?id=$cm->id&amp;frameset=top";
        $coursecontext = context_course::instance($course->id);
        $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
        $title = strip_tags($courseshortname.': '.format_string($url2->name));
        $framesize = $config->framesize;
        $modulename = s(get_string('modulename','url2'));
        $contentframetitle = s(format_string($url2->name));
        $dir = get_string('thisdirection', 'langconfig');

        $extframe = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navurl2" title="$modulename"/>
    <frame src="$exteurl2" title="$contentframetitle"/>
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');
        echo $extframe;
        die;
    }
}

/**
 * Print url2 info and link.
 * @param object $url2
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function url2_print_workaround($url2, $cm, $course) {
    global $OUTPUT;

    url2_print_header($url2, $cm, $course);
    url2_print_heading($url2, $cm, $course, true);
    url2_print_intro($url2, $cm, $course, true);

    $fullurl2 = url2_get_full_url2($url2, $cm, $course);

    $display = url2_get_final_display_type($url2);
    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $jsfullurl2 = addslashes_js($fullurl2);
        $options = empty($url2->displayoptions) ? array() : unserialize($url2->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $extra = "onclick=\"window.open('$jsfullurl2', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $extra = "onclick=\"this.target='_blank';\"";

    } else {
        $extra = '';
    }

    echo '<div class="url2workaround">';
    print_string('clicktoopen', 'url2', "<a href=\"$fullurl2\" $extra>$fullurl2</a>");
    echo '</div>';

    echo $OUTPUT->footer();
    die;
}

/**
 * Display embedded url2 file.
 * @param object $url2
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function url2_display_embed($url2, $cm, $course) {
    global $CFG, $PAGE, $OUTPUT;

    $mimetype = resourcelib_guess_url2_mimetype($url2->externalurl2);
    $fullurl2  = url2_get_full_url2($url2, $cm, $course);
    $title    = $url2->name;

    $link = html_writer::tag('a', $fullurl2, array('href'=>str_replace('&amp;', '&', $fullurl2)));
    $clicktoopen = get_string('clicktoopen', 'url2', $link);
    $moodleurl2 = new moodle_url($fullurl2);

    $extension = resourcelib_get_extension($url2->externalurl2);

    $mediamanager = core_media_manager::instance($PAGE);
    $embedoptions = array(
        core_media_manager::OPTION_TRUSTED => true,
        core_media_manager::OPTION_BLOCK => true
    );

    if (in_array($mimetype, array('image/gif','image/jpeg','image/png'))) {  // It's an image
        $code = resourcelib_embed_image($fullurl2, $title);

    } else if ($mediamanager->can_embed_url2($moodleurl2, $embedoptions)) {
        // Media (audio/video) file.
        $code = $mediamanager->embed_url2($moodleurl2, $title, 0, 0, $embedoptions);

    } else {
        // anything else - just try object tag enlarged as much as possible
        $code = resourcelib_embed_general($fullurl2, $title, $clicktoopen, $mimetype);
    }

    url2_print_header($url2, $cm, $course);
    url2_print_heading($url2, $cm, $course);

    echo $code;

    url2_print_intro($url2, $cm, $course);

    echo $OUTPUT->footer();
    die;
}

/**
 * Decide the best display format.
 * @param object $url2
 * @return int display type constant
 */
function url2_get_final_display_type($url2) {
    global $CFG;

    if ($url2->display != RESOURCELIB_DISPLAY_AUTO) {
        return $url2->display;
    }

    // detect links to local moodle pages
    if (strpos($url2->externalurl2, $CFG->wwwroot) === 0) {
        if (strpos($url2->externalurl2, 'file.php') === false and strpos($url2->externalurl2, '.php') !== false ) {
            // most probably our moodle page with navigation
            return RESOURCELIB_DISPLAY_OPEN;
        }
    }

    static $download = array('application/zip', 'application/x-tar', 'application/g-zip',     // binary formats
                             'application/pdf', 'text/html');  // these are known to cause trouble for external links, sorry
    static $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml',         // images
                             'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
                             'video/quicktime', 'video/mpeg', 'video/mp4',
                             'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats,
                            );

    $mimetype = resourcelib_guess_url2_mimetype($url2->externalurl2);

    if (in_array($mimetype, $download)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (in_array($mimetype, $embed)) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
}

/**
 * Get the parameters that may be appended to url2
 * @param object $config url2 module config options
 * @return array array describing opt groups
 */
function url2_get_variable_options($config) {
    global $CFG;

    $options = array();
    $options[''] = array('' => get_string('chooseavariable', 'url2'));

    $options[get_string('course')] = array(
        'courseid'        => 'id',
        'coursefullname'  => get_string('fullnamecourse'),
        'courseshortname' => get_string('shortnamecourse'),
        'courseidnumber'  => get_string('idnumbercourse'),
        'coursesummary'   => get_string('summary'),
        'courseformat'    => get_string('format'),
    );

    $options[get_string('modulename', 'url2')] = array(
        'url2instance'     => 'id',
        'url2cmid'         => 'cmid',
        'url2name'         => get_string('name'),
        'url2idnumber'     => get_string('idnumbermod'),
    );

    $options[get_string('miscellaneous')] = array(
        'sitename'        => get_string('fullsitename'),
        'serverurl2'       => get_string('serverurl2', 'url2'),
        'currenttime'     => get_string('time'),
        'lang'            => get_string('language'),
    );
    if (!empty($config->secretphrase)) {
        $options[get_string('miscellaneous')]['encryptedcode'] = get_string('encryptedcode');
    }

    $options[get_string('user')] = array(
        'userid'          => 'id',
        'userusername'    => get_string('username'),
        'useridnumber'    => get_string('idnumber'),
        'userfirstname'   => get_string('firstname'),
        'userlastname'    => get_string('lastname'),
        'userfullname'    => get_string('fullnameuser'),
        'useremail'       => get_string('email'),
        'usericq'         => get_string('icqnumber'),
        'userphone1'      => get_string('phone1'),
        'userphone2'      => get_string('phone2'),
        'userinstitution' => get_string('institution'),
        'userdepartment'  => get_string('department'),
        'useraddress'     => get_string('address'),
        'usercity'        => get_string('city'),
        'usertimezone'    => get_string('timezone'),
        'userurl2'         => get_string('webpage'),
    );

    if ($config->rolesinparams) {
        $roles = role_fix_names(get_all_roles());
        $roleoptions = array();
        foreach ($roles as $role) {
            $roleoptions['course'.$role->shortname] = get_string('yourwordforx', '', $role->localname);
        }
        $options[get_string('roles')] = $roleoptions;
    }

    return $options;
}

/**
 * Get the parameter values that may be appended to url2
 * @param object $url2 module instance
 * @param object $cm
 * @param object $course
 * @param object $config module config options
 * @return array of parameter values
 */
function url2_get_variable_values($url2, $cm, $course, $config) {
    global $USER, $CFG;

    $site = get_site();

    $coursecontext = context_course::instance($course->id);

    $values = array (
        'courseid'        => $course->id,
        'coursefullname'  => format_string($course->fullname),
        'courseshortname' => format_string($course->shortname, true, array('context' => $coursecontext)),
        'courseidnumber'  => $course->idnumber,
        'coursesummary'   => $course->summary,
        'courseformat'    => $course->format,
        'lang'            => current_language(),
        'sitename'        => format_string($site->fullname),
        'serverurl2'       => $CFG->wwwroot,
        'currenttime'     => time(),
        'url2instance'     => $url2->id,
        'url2cmid'         => $cm->id,
        'url2name'         => format_string($url2->name),
        'url2idnumber'     => $cm->idnumber,
    );

    if (isloggedin()) {
        $values['userid']          = $USER->id;
        $values['userusername']    = $USER->username;
        $values['useridnumber']    = $USER->idnumber;
        $values['userfirstname']   = $USER->firstname;
        $values['userlastname']    = $USER->lastname;
        $values['userfullname']    = fullname($USER);
        $values['useremail']       = $USER->email;
        $values['usericq']         = $USER->icq;
        $values['userphone1']      = $USER->phone1;
        $values['userphone2']      = $USER->phone2;
        $values['userinstitution'] = $USER->institution;
        $values['userdepartment']  = $USER->department;
        $values['useraddress']     = $USER->address;
        $values['usercity']        = $USER->city;
        $now = new DateTime('now', core_date::get_user_timezone_object());
        $values['usertimezone']    = $now->getOffset() / 3600.0; // Value in hours for BC.
        $values['userurl2']         = $USER->url2;
    }

    // weak imitation of Single-Sign-On, for backwards compatibility only
    // NOTE: login hack is not included in 2.0 any more, new contrib auth plugin
    //       needs to be createed if somebody needs the old functionality!
    if (!empty($config->secretphrase)) {
        $values['encryptedcode'] = url2_get_encrypted_parameter($url2, $config);
    }

    //hmm, this is pretty fragile and slow, why do we need it here??
    if ($config->rolesinparams) {
        $coursecontext = context_course::instance($course->id);
        $roles = role_fix_names(get_all_roles($coursecontext), $coursecontext, ROLENAME_ALIAS);
        foreach ($roles as $role) {
            $values['course'.$role->shortname] = $role->localname;
        }
    }

    return $values;
}

/**
 * BC internal function
 * @param object $url2
 * @param object $config
 * @return string
 */
function url2_get_encrypted_parameter($url2, $config) {
    global $CFG;

    if (file_exists("$CFG->dirroot/local/externserverfile.php")) {
        require_once("$CFG->dirroot/local/externserverfile.php");
        if (function_exists('extern_server_file')) {
            return extern_server_file($url2, $config);
        }
    }
    return md5(getremoteaddr().$config->secretphrase);
}

/**
 * Optimised mimetype detection from general url2
 * @param $fullurl2
 * @param int $size of the icon.
 * @return string|null mimetype or null when the filetype is not relevant.
 */
function url2_guess_icon($fullurl2, $size = null) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");

    if (substr_count($fullurl2, '/') < 3 or substr($fullurl2, -1) === '/') {
        // Most probably default directory - index.php, index.html, etc. Return null because
        // we want to use the default module icon instead of the HTML file icon.
        return null;
    }

    $icon = file_extension_icon($fullurl2, $size);
    $htmlicon = file_extension_icon('.htm', $size);
    $unknownicon = file_extension_icon('', $size);

    // We do not want to return those icon types, the module icon is more appropriate.
    if ($icon === $unknownicon || $icon === $htmlicon) {
        return null;
    }

    return $icon;
}
