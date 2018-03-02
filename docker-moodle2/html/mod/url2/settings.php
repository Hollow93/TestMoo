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
 * url2 module admin settings and defaults
 *
 * @package    mod_url2
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_FRAME,
                                                           RESOURCELIB_DISPLAY_OPEN,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
                                   RESOURCELIB_DISPLAY_EMBED,
                                   RESOURCELIB_DISPLAY_OPEN,
                                   RESOURCELIB_DISPLAY_POPUP,
                                  );

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('url2/framesize',
        get_string('framesize', 'url2'), get_string('configframesize', 'url2'), 130, PARAM_INT));
    $settings->add(new admin_setting_configpasswordunmask('url2/secretphrase', get_string('password'),
        get_string('configsecretphrase', 'url2'), ''));
    $settings->add(new admin_setting_configcheckbox('url2/rolesinparams',
        get_string('rolesinparams', 'url2'), get_string('configrolesinparams', 'url2'), false));
    $settings->add(new admin_setting_configmultiselect('url2/displayoptions',
        get_string('displayoptions', 'url2'), get_string('configdisplayoptions', 'url2'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('url2modeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('url2/printintro',
        get_string('printintro', 'url2'), get_string('printintroexplain', 'url2'), 1));
    $settings->add(new admin_setting_configselect('url2/display',
        get_string('displayselect', 'url2'), get_string('displayselectexplain', 'url2'), RESOURCELIB_DISPLAY_AUTO, $displayoptions));
    $settings->add(new admin_setting_configtext('url2/popupwidth',
        get_string('popupwidth', 'url2'), get_string('popupwidthexplain', 'url2'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('url2/popupheight',
        get_string('popupheight', 'url2'), get_string('popupheightexplain', 'url2'), 450, PARAM_INT, 7));
}
