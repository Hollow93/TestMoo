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
 * Unit tests for some mod url2 lib stuff.
 *
 * @package    mod_url2
 * @category   phpunit
 * @copyright  2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * mod_url2 tests
 *
 * @package    mod_url2
 * @category   phpunit
 * @copyright  2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_url2_lib_testcase extends advanced_testcase {

    /**
     * Prepares things before this test case is initialised
     * @return void
     */
    public static function setUpBeforeClass() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/url2/lib.php');
        require_once($CFG->dirroot . '/mod/url2/locallib.php');
    }

    /**
     * Tests the url2_appears_valid_url2 function
     * @return void
     */
    public function test_url2_appears_valid_url2() {
        $this->assertTrue(url2_appears_valid_url2('http://example'));
        $this->assertTrue(url2_appears_valid_url2('http://www.example.com'));
        $this->assertTrue(url2_appears_valid_url2('http://www.exa-mple2.com'));
        $this->assertTrue(url2_appears_valid_url2('http://www.example.com/~nobody/index.html'));
        $this->assertTrue(url2_appears_valid_url2('http://www.example.com#hmm'));
        $this->assertTrue(url2_appears_valid_url2('http://www.example.com/#hmm'));
        $this->assertTrue(url2_appears_valid_url2('http://www.example.com/žlutý koníček/lala.txt'));
        $this->assertTrue(url2_appears_valid_url2('http://www.example.com/žlutý koníček/lala.txt#hmmmm'));
        $this->assertTrue(url2_appears_valid_url2('http://www.example.com/index.php?xx=yy&zz=aa'));
        $this->assertTrue(url2_appears_valid_url2('https://user:password@www.example.com/žlutý koníček/lala.txt'));
        $this->assertTrue(url2_appears_valid_url2('ftp://user:password@www.example.com/žlutý koníček/lala.txt'));

        $this->assertFalse(url2_appears_valid_url2('http:example.com'));
        $this->assertFalse(url2_appears_valid_url2('http:/example.com'));
        $this->assertFalse(url2_appears_valid_url2('http://'));
        $this->assertFalse(url2_appears_valid_url2('http://www.exa mple.com'));
        $this->assertFalse(url2_appears_valid_url2('http://www.examplé.com'));
        $this->assertFalse(url2_appears_valid_url2('http://@www.example.com'));
        $this->assertFalse(url2_appears_valid_url2('http://user:@www.example.com'));

        $this->assertTrue(url2_appears_valid_url2('lalala://@:@/'));
    }

    /**
     * Test url2_view
     * @return void
     */
    public function test_url2_view() {
        global $CFG;

        $CFG->enablecompletion = 1;
        $this->resetAfterTest();

        // Setup test data.
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $url2 = $this->getDataGenerator()->create_module('url2', array('course' => $course->id),
                                                            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($url2->cmid);
        $cm = get_coursemodule_from_instance('url2', $url2->id);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();

        $this->setAdminUser();
        url2_view($url2, $course, $cm, $context);

        $events = $sink->get_events();
        // 2 additional events thanks to completion.
        $this->assertCount(3, $events);
        $event = array_shift($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_url2\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $url2 = new \moodle_url('/mod/url2/view.php', array('id' => $cm->id));
        $this->assertEquals($url2, $event->get_url2());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

        // Check completion status.
        $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);
    }

    public function test_url2_core_calendar_provide_event_action() {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create the activity.
        $course = $this->getDataGenerator()->create_course();
        $url2 = $this->getDataGenerator()->create_module('url2', array('course' => $course->id));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $url2->id,
            \core_completion\api::COMPLETION_EVENT_TYPE_DATE_COMPLETION_EXPECTED);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_url2_core_calendar_provide_event_action($event, $factory);

        // Confirm the event was decorated.
        $this->assertInstanceOf('\core_calendar\local\event\value_objects\action', $actionevent);
        $this->assertEquals(get_string('view'), $actionevent->get_name());
        $this->assertInstanceOf('moodle_url2', $actionevent->get_url2());
        $this->assertEquals(1, $actionevent->get_item_count());
        $this->assertTrue($actionevent->is_actionable());
    }

    public function test_url2_core_calendar_provide_event_action_already_completed() {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();

        $CFG->enablecompletion = 1;

        // Create the activity.
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $url2 = $this->getDataGenerator()->create_module('url2', array('course' => $course->id),
            array('completion' => 2, 'completionview' => 1, 'completionexpected' => time() + DAYSECS));

        // Get some additional data.
        $cm = get_coursemodule_from_instance('url2', $url2->id);

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $url2->id,
            \core_completion\api::COMPLETION_EVENT_TYPE_DATE_COMPLETION_EXPECTED);

        // Mark the activity as completed.
        $completion = new completion_info($course);
        $completion->set_module_viewed($cm);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_url2_core_calendar_provide_event_action($event, $factory);

        // Ensure result was null.
        $this->assertNull($actionevent);
    }

    /**
     * Creates an action event.
     *
     * @param int $courseid The course id.
     * @param int $instanceid The instance id.
     * @param string $eventtype The event type.
     * @return bool|calendar_event
     */
    private function create_action_event($courseid, $instanceid, $eventtype) {
        $event = new stdClass();
        $event->name = 'Calendar event';
        $event->modulename  = 'url2';
        $event->courseid = $courseid;
        $event->instance = $instanceid;
        $event->type = CALENDAR_EVENT_TYPE_ACTION;
        $event->eventtype = $eventtype;
        $event->timestart = time();

        return calendar_event::create($event);
    }
}