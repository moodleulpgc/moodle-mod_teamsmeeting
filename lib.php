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
 * Library of interface functions and constants.
 *
 * @package     mod_teamsmeeting
 * @copyright   2020 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('TEAMSMEETING_MEMBER_AUTO', 0);
define('TEAMSMEETING_MEMBER_MANUAL', 1);
define('TEAMSMEETING_MEMBER_CHANNEL', 2);

define('TEAMSMEETING_PRESENTER_ANY', 'everyone'); //Everyone is a presenter (This is default option).
define('TEAMSMEETING_PRESENTER_ALL', 'organization'); //Everyone in organizer’s organization is a presenter.
define('TEAMSMEETING_PRESENTER_CHANNEL', 'channel'); // Does nothing, roles delegated to o365 channel membership
define('TEAMSMEETING_PRESENTER_BYROLE', 'roleIsPresenter'); //Only the participants whose role is presenter are presenters.
define('TEAMSMEETING_PRESENTER_ORG', 'organizer'); //Only the organizer is a presenter.
define('TEAMSMEETING_PRESENTER_COURSE', 'course'); //All and any teacher in course is a presenter.
define('TEAMSMEETING_PRESENTER_GROUP', 'group'); //Only the group teachers are presenters. Teachers with accessallgroups included

define('TEAMSMEETING_ROLE_ATTENDEE', 'attendee');
define('TEAMSMEETING_ROLE_PRESENTER', 'presenter');
define('TEAMSMEETING_ROLE_PRODUCER', 'producer');

define('TEAMSMEETING_LOBBY_NONE', 'organizer'); //Only the organizer is admitted into the meeting, bypassing the lobby. All other participants are placed in the meeting lobby.
define('TEAMSMEETING_LOBBY_ORG', 'organization'); //Only the participants from the same company are admitted into the meeting, bypassing the lobby. All other participants are placed in the meeting lobby.
define('TEAMSMEETING_LOBBY_ORGFED', 'organizationAndFederated'); //Only the participants from the same company or trusted organization are admitted into the meeting, bypassing the lobby. All other participants are placed in the meeting lobby.
define('TEAMSMEETING_LOBBY_ALL', 'everyone'); //Everyone is admitted into the meeting. No participants are placed in the meeting lobby.

define('OLMEETING_STATE_OK', 0);
define('OLMEETING_STATE_UPDATE', 1);
define('OLMEETING_STATE_EXPIRED', 2);
define('OLMEETING_STATE_OFF', -1);

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function teamsmeeting_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_teamsmeeting into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $teamsmeeting An object from the form.
 * @param mod_teamsmeeting_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function teamsmeeting_add_instance($teamsmeeting, $mform = null) {
    global $DB;

    $teamsmeeting->timecreated = time();
    $teamsmeeting->timemodified = $teamsmeeting->timecreated;

    $teamsmeeting->id = $DB->insert_record('teamsmeeting', $teamsmeeting);

    // Add calendar events if necessary.
    teamsmeeting_set_events($teamsmeeting);
    
    return $teamsmeeting->id;
}

/**
 * Updates an instance of the mod_teamsmeeting in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $teamsmeeting An object from the form in mod_form.php.
 * @param mod_teamsmeeting_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function teamsmeeting_update_instance($teamsmeeting, $mform = null) {
    global $CFG, $DB;

    $teamsmeeting->timemodified = time();
    $teamsmeeting->id = $teamsmeeting->instance;

    $oldrec = $DB->get_record('teamsmeeting', ['id' => $teamsmeeting->instance]);
    
    if($success = $DB->update_record('teamsmeeting', $teamsmeeting)) {
    
        $params = ['teamsmeetingid' => $teamsmeeting->id, 'status' => OLMEETING_STATE_OK ];
        if($onlinemeetings =  $DB->get_records('teamsmeeting_onlinemeeting', $params)) {
            include_once($CFG->dirroot.'/mod/teamsmeeting/locallib.php');
            $updateremote = get_config('teamsmeeting',  'updateremote');
            foreach($onlinemeetings as $onlinemeeting) {
                if($onlinemeeting->groupid) {
                    $teamsmeeting = teamsmeeting_update_group_dates($teamsmeeting, $onlinemeeting->groupid);
                }                
                if($updateremote && $update = teamsmeeting_checkupdating_onlinemeetings($teamsmeeting, $oldrec)) {
                    teamsmeeting_update_onlinemeeting($teamsmeeting, $onlinemeeting, $update); 
                }
            }        
        }
    }
    
    // Add calendar events if necessary.
    teamsmeeting_set_events($teamsmeeting);
    
    return $success;
}

/**
 * Removes an instance of the mod_teamsmeeting from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function teamsmeeting_delete_instance($id) {
    global $DB;

    if(!$teamsmeeting = $DB->get_record('teamsmeeting', ['id' => $id])) {
        return false;
    }

    if($recordings = $DB->get_records_menu('teamsmeeting_onlinemeeting', ['teamsmeetingid' => $id], 
                                            null, 'id, teamsmeetingid')) {
        $DB->delete_records_list('teamsmeeting_recording', 'onlinemeetingid', array_keys($recordings));
    }
    
    if($onlinemeetings = $DB->get_records('teamsmeeting_onlinemeeting', ['teamsmeetingid' => $id])) {
        $deleted = [];
        if($deleteremote = get_config('teamsmeeting',  'deleteremote')) {
            foreach($onlinemeetings as $oid => $onlinemeeting) {
                $o365api = \mod_teamsmeeting\rest\unified::get_o365api($teamsmeeting, $onlinemeeting->teacherid); 
                if($o365api->delete_onlinemeeting($onlinemeeting->meetingid)) {
                    $deleted[] = $oid;
                }
                unset($o365api);
            }
            if($deleted) {
                $DB->delete_records_list('teamsmeeting_onlinemeeting', 'id', $deleted);
            }
        } else {
            $deleted = array_keys($onlinemeetings);
        }
    }
    
    $DB->delete_records('teamsmeeting_overrides', ['teamsmeetingid' => $id]);
    $DB->delete_records('teamsmeeting', ['id' => $id]);

    // Remove old calendar events.
    if (!$DB->delete_records('event', ['modulename' => 'teamsmeeting', 'instance' => $id])) {
        $result = false;
    }
    
    return true;
}

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}.
 *
 * @package     mod_teamsmeeting
 * @category    files
 *
 * @param stdClass $course.
 * @param stdClass $cm.
 * @param stdClass $context.
 * @return string[].
 */
function teamsmeeting_get_file_areas($course, $cm, $context) {
    return [];
}

/**
 * File browsing support for mod_teamsmeeting file areas.
 *
 * @package     mod_teamsmeeting
 * @category    files
 *
 * @param file_browser $browser.
 * @param array $areas.
 * @param stdClass $course.
 * @param stdClass $cm.
 * @param stdClass $context.
 * @param string $filearea.
 * @param int $itemid.
 * @param string $filepath.
 * @param string $filename.
 * @return file_info Instance or null if not found.
 */
function teamsmeeting_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the mod_teamsmeeting file areas.
 *
 * @package     mod_teamsmeeting
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The mod_teamsmeeting's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function teamsmeeting_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = []) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);
    send_file_not_found();
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function teamsmeeting_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-teamsmeeting-*'=>get_string('page-mod-teamsmeeting-x', 'teamsmeeting'));
    return $module_pagetype;
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $teamsmeeting     teamsmeeting object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function teamsmeeting_view($teamsmeeting, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $teamsmeeting->id
    );

    $event = \mod_teamsmeeting\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('teamsmeeting', $teamsmeeting);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}


/**
 * Add a get_coursemodule_info function in case any teamsmeeting type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function teamsmeeting_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    $fields = 'id, name, intro, introformat, openingtime, closingtime';
    if (!$teamsmeeting = $DB->get_record('teamsmeeting', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $teamsmeeting->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('teamsmeeting', $teamsmeeting, $coursemodule->id, false);
    }

    // Populate some other values that can be used in calendar or on dashboard.
    if ($teamsmeeting->openingtime) {
        $result->customdata['openingtime'] = $teamsmeeting->openingtime;
    }
    if ($teamsmeeting->closingtime) {
        $result->customdata['closingtime'] = $teamsmeeting->closingtime;
    }

    return $result;
}


/**
 * This creates new calendar events given as timeopen and timeclose by $teamsmeeting.
 *
 * @param stdClass $teamsmeeting
 * @return void
 */
function teamsmeeting_set_events($teamsmeeting) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/calendar/lib.php');
    
    $overrides = $DB->get_records('teamsmeeting_overrides', ['teamsmeetingid' => $teamsmeeting->id]);
    $teamsmeeting->groupid = 0;
    $overrides[0] = $teamsmeeting;
    
    foreach($overrides as $meeting) {
        // only use openingtime, closingtime & groupid
    
    
        // TODO  // TODO  // TODO  // TODO  // TODO  // TODO  // TODO  // TODO  // TODO  // TODO  
    
    }
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every teamsmeeting event in the site is checked, else
 * only teamsmeeting events belonging to the course specified are checked.
 * This function is used, in its new format, by restore_refresh_events()
 *
 * @param int $courseid
 * @param int|stdClass $instance teamsmeeting module instance or ID.
 * @param int|stdClass $cm Course module object or ID (not used in this module).
 * @return bool
 */
function teamsmeeting_refresh_events($courseid = 0, $instance = null, $cm = null) {
    global $DB, $CFG;
    // TODO  // TODO  // TODO  // TODO  // TODO  
}


/**
 * Extends the settings navigation with the teamsmeeting settings.
 *
 * This function is called when the context for the page is a teamsmeeting module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $teamsmeetingnode {@link navigation_node}
 */
function teamsmeeting_extend_settings_navigation(settings_navigation $settings, navigation_node $navref) {
    global $CFG, $PAGE;
    
    if (!$PAGE->cm) {
        return;
    }

    if (!$PAGE->course) {
        return;
    }
    
    $link = new moodle_url('/mod/teamsmeeting/overrides.php', ['id'=>$PAGE->cm->id]);
    
    if (has_all_capabilities(['mod/teamsmeeting:create', 'moodle/site:accessallgroups'], $PAGE->cm->context)) {
        $link->param('action', 'notify');
        $node = $navref->add(get_string('overrideslink', 'teamsmeeting'), $link, navigation_node::TYPE_SETTING, null, 'teamsmeetingnotify', new pix_icon('t/email', ''));
    }
    
}
