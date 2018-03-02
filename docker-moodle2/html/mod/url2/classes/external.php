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
 * url2 external API
 *
 * @package    mod_url2
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

/**
 * url2 external functions
 *
 * @package    mod_url2
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_url2_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function view_url2_parameters() {
        return new external_function_parameters(
            array(
                'url2id' => new external_value(PARAM_INT, 'url2 instance id')
            )
        );
    }

    /**
     * Trigger the course module viewed event and update the module completion status.
     *
     * @param int $url2id the url2 instance id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function view_url2($url2id) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/url2/lib.php");

        $params = self::validate_parameters(self::view_url2_parameters(),
                                            array(
                                                'url2id' => $url2id
                                            ));
        $warnings = array();

        // Request and permission validation.
        $url2 = $DB->get_record('url2', array('id' => $params['url2id']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($url2, 'url2');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/url2:view', $context);

        // Call the url2/lib API.
        url2_view($url2, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function view_url2_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Describes the parameters for get_url2s_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_url2s_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Returns a list of url2s in a provided list of courses.
     * If no list is provided all url2s that the user can view will be returned.
     *
     * @param array $courseids course ids
     * @return array of warnings and url2s
     * @since Moodle 3.3
     */
    public static function get_url2s_by_courses($courseids = array()) {

        $warnings = array();
        $returnedurl2s = array();

        $params = array(
            'courseids' => $courseids,
        );
        $params = self::validate_parameters(self::get_url2s_by_courses_parameters(), $params);

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

            // Get the url2s in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $url2s = get_all_instances_in_courses("url2", $courses);
            foreach ($url2s as $url2) {
                $context = context_module::instance($url2->coursemodule);
                // Entry to return.
                $url2->name = external_format_string($url2->name, $context->id);

                list($url2->intro, $url2->introformat) = external_format_text($url2->intro,
                                                                $url2->introformat, $context->id, 'mod_url2', 'intro', null);
                $url2->introfiles = external_util::get_area_files($context->id, 'mod_url2', 'intro', false, false);

                $returnedurl2s[] = $url2;
            }
        }

        $result = array(
            'url2s' => $returnedurl2s,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_url2s_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_url2s_by_courses_returns() {
        return new external_single_structure(
            array(
                'url2s' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Module id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'url2 name'),
                            'intro' => new external_value(PARAM_RAW, 'Summary'),
                            'introformat' => new external_format_value('intro', 'Summary format'),
                            'introfiles' => new external_files('Files in the introduction text'),
                            'externalurl2' => new external_value(PARAM_RAW_TRIMMED, 'External url2'),
                            'display' => new external_value(PARAM_INT, 'How to display the url2'),
                            'displayoptions' => new external_value(PARAM_RAW, 'Display options (width, height)'),
                            'parameters' => new external_value(PARAM_RAW, 'Parameters to append to the url2'),
                            'timemodified' => new external_value(PARAM_INT, 'Last time the url2 was modified'),
                            'section' => new external_value(PARAM_INT, 'Course section id'),
                            'visible' => new external_value(PARAM_INT, 'Module visibility'),
                            'groupmode' => new external_value(PARAM_INT, 'Group mode'),
                            'groupingid' => new external_value(PARAM_INT, 'Grouping id'),
                        )
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }
}
