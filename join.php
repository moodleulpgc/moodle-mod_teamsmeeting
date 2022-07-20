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
 * Join to a meeting, after registering action.
 *
 * @package     mod_teamsmeeting
 * @copyright   2020 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$t  = optional_param('t', 0, PARAM_INT);

if ($id) {
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'teamsmeeting');
    $teamsmeeting = $DB->get_record('teamsmeeting', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($t) {
    $teamsmeeting = $DB->get_record('teamsmeeting', array('id' => $t), '*', MUST_EXIST);
    list ($course, $cm) = get_course_and_cm_from_instance($t,  'teamsmeeting');
} else {
    print_error(get_string('missingidandcmid', 'mod_teamsmeeting'));
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

// set url params 
$urlparams = array('id' => $cm->id);
/// Check to see if groups are being used in this examboard
$groupmode = groups_get_activity_groupmode($cm);
$groupid = 0;
if ($groupmode) {
    $groupid = groups_get_activity_group($cm, true);
}

$oid = 0;
$meetingurl = '';
$rid = optional_param('rid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
if ($rid) { 
    $recording = $DB->get_record('teamsmeeting_recording', ['id' => $rid], '*', MUST_EXIST);
    $oid = $recording->onlinemeetingid;
    $meetingurl = $recording->streamurl;
}  

if($oid = ($oid) ? $oid : optional_param('oid', 0, PARAM_INT)) {
    $onlinemeeting = $DB->get_record('teamsmeeting_onlinemeeting', array('id'=>$oid)); 
    $groupid = $onlinemeeting->groupid;
    $action = 'join';
}

if($groupid) {
    $urlparams['group'] = $groupid;
}

$baseurl = new moodle_url('/mod/teamsmeeting/view.php', $urlparams);

$PAGE->set_url($baseurl);
$PAGE->set_title(format_string($teamsmeeting->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_activity_record($teamsmeeting);

$meetingurl =  ($meetingurl) ? $meetingurl :  optional_param('joinurl', '', PARAM_URL);

// build common event data
$eventparams = ['context' => $context,
                'objectid' => $teamsmeeting->id,
                'userid' => $USER->id,
                'other' => ['meetingid' => $oid],
                ];

if($meetingurl && !$action) {    
    redirect($meetingurl);                
    
} elseif($action == 'join') {
    $activate = optional_param('activate', 0, PARAM_INT);
    
    $eventparams['other']['activate'] = $activate;
    
    // sets teacher joined 
    if($activate && has_capability('mod/teamsmeeting:create', $context)) {
        $params = array('teamsmeetingid' => $teamsmeeting->id, 'id' => $oid, 'expired' => 0 );
        $DB->set_field('teamsmeeting_onlinemeeting', 'timeactivated', $activate, $params);
    }

    // sets joined event 
    $event = \mod_teamsmeeting\event\meeting_joined::create($eventparams);
    $event->trigger();

    $message = ($meetingurl == $baseurl) ? get_string('errornomeeting', 'teamsmeeting') : '';
    redirect($meetingurl, $message);
}

// If action != join, we are viewing a recording

$PAGE->navbar->add(get_string('recording', 'teamsmeeting'), null);
$renderer = $PAGE->get_renderer('mod_teamsmeeting');
echo $renderer->header();
echo $renderer->heading(format_string($teamsmeeting->name), 2, null);

$rid = optional_param('rid', 0, PARAM_INT);
if($recording = $DB->get_record('teamsmeeting_recording', ['id' => $rid, 'onlinemeetingid' => $oid])) {
    echo $renderer->heading(format_string($recording->name), 3, null);

    // echo DESCRIPTION

    // sets joined event 
    $eventparams['other']['rid'] = $rid;
    //$event = \mod_teamsmeeting\event\recording_viewed::create($eventparams);
    //$event->trigger();
    
    
    echo $renderer->show_player_iframe($meetingurl);
} else {
    //nothing to see
    echo $renderer->box(get_string('nothingtosee', 'teamsmeeting'), 'box centerpara  alert-warning text-danger');
}




echo $renderer->single_button($baseurl, get_string('backtomodule', 'teamsmeeting'), 'get', array('class' => 'backtocourse clearfix'));

$back = new moodle_url('/course/view.php', array('id' => $course->id));
echo $renderer->single_button($back, get_string('backtocourse', 'teamsmeeting'), 'get', array('class' => 'backtocourse clearfix'));


echo $renderer->footer();
