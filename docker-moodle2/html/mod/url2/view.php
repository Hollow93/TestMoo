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
 * url2 module main user interface
 *
 * @package    mod_url2
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/url2/lib.php");
require_once("$CFG->dirroot/mod/url2/locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // url2 instance id
$redirect = optional_param('redirect', 0, PARAM_BOOL);

if ($u) {  // Two ways to specify the module
    $url2 = $DB->get_record('url2', array('id'=>$u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('url2', $url2->id, $url2->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('url2', $id, 0, false, MUST_EXIST);
    $url2 = $DB->get_record('url2', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/url2:view', $context);

// Completion and trigger events.
url2_view($url2, $course, $cm, $context);

$PAGE->set_url2('/mod/url2/view.php', array('id' => $cm->id));

// Make sure url2 exists before generating output - some older sites may contain empty url2s
// Do not use PARAM_url2 here, it is too strict and does not support general URIs!
$exturl2 = trim($url2->externalurl2);
if (empty($exturl2) or $exturl2 === 'http://') {
    url2_print_header($url2, $cm, $course);
    url2_print_heading($url2, $cm, $course);
    url2_print_intro($url2, $cm, $course);
    notice(get_string('invalidstoredurl2', 'url2'), new moodle_url('/course/view.php', array('id'=>$cm->course)));
    die;
}
unset($exturl2);

$displaytype = url2_get_final_display_type($url2);
if ($displaytype == RESOURCELIB_DISPLAY_OPEN) {
    // For 'open' links, we always redirect to the content - except if the user
    // just chose 'save and display' from the form then that would be confusing
    if (strpos(get_local_referer(false), 'modedit.php') === false) {
        $redirect = true;
    }
}

if ($redirect) {
    // coming from course page or url2 index page,
    // the redirection is needed for completion tracking and logging
    $fullurl2 = str_replace('&amp;', '&', url2_get_full_url2($url2, $cm, $course));

    if (!course_get_format($course)->has_view_page()) {
        // If course format does not have a view page, add redirection delay with a link to the edit page.
        // Otherwise teacher is redirected to the external url2 without any possibility to edit activity or course settings.
        $editurl2 = null;
        if (has_capability('moodle/course:manageactivities', $context)) {
            $editurl2 = new moodle_url('/course/modedit.php', array('update' => $cm->id));
            $edittext = get_string('editthisactivity');
        } else if (has_capability('moodle/course:update', $context->get_course_context())) {
            $editurl2 = new moodle_url('/course/edit.php', array('id' => $course->id));
            $edittext = get_string('editcoursesettings');
        }
        if ($editurl2) {
            redirect($fullurl2, html_writer::link($editurl2, $edittext)."<br/>".
                    get_string('pageshouldredirect'), 10);
        }
    }
    redirect($fullurl2);
}

switch ($displaytype) {
    case RESOURCELIB_DISPLAY_EMBED:
        url2_display_embed($url2, $cm, $course);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        url2_display_frame($url2, $cm, $course);
        break;
    default:
        url2_print_workaround($url2, $cm, $course);
        break;
}
