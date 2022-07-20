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
 * Add/edit recording to a meeting
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

$oid = required_param('oid', PARAM_INT);
$onlinemeeting = $DB->get_record('teamsmeeting_onlinemeeting', array('id' => $oid), '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
// set url params 
$urlparams = array('id' => $cm->id);

/// Check to see if groups are being used in this examboard
if($onlinemeeting->groupid) {
    $urlparams['group'] = $onlinemeeting->groupid;
}

$baseurl = new moodle_url('/mod/teamsmeeting/view.php', $urlparams);

$PAGE->set_url($baseurl);
$PAGE->set_title(format_string($teamsmeeting->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_activity_record($teamsmeeting);
$PAGE->navbar->add(get_string('recordings', 'teamsmeeting'), null);

////////// Actions  
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$item = optional_param('item', 0, PARAM_INT); 

if($item) {
    $recording = $DB->get_record('teamsmeeting_recording', ['id' => $item]);
    unset($recording->id);    
} else {
    $recording = new stdclass();
}

//// Non interface actions 
if(($action == 'del') || ($action == 'vis'))  {
    if($action == 'del') {
        if($DB->delete_records('teamsmeeting_recording', ['id' => $item])) {
            \core\notification::add(get_string('recordingdeleted', 'teamsmeeting', $recording->name), 
                                                \core\output\notification::NOTIFY_SUCCESS);
        }
    }

    if($action == 'vis') {
        $visible = !$recording->visible;
        $DB->set_field('teamsmeeting_recording', 'visible', $visible, ['id' => $item]);
    }
    redirect($baseurl);
}

$straction = get_string($action.'recording', 'teamsmeeting');
$mform = new \mod_teamsmeeting\form\recording_form(null, ['item' => $item]);

$recording->id = $cm->id;
$recording->oid = $onlinemeeting->id;
$mform->set_data($recording);

    // If data has been uploaded, then process it
    if ($mform->is_cancelled()) {
        redirect($baseurl);

    } else if ($fromform = $mform->get_data()) {
        $recording->onlinemeetingid = $oid;
        $recording->name = $fromform->name;
        $recording->streamurl = $fromform->streamurl;
        $recording->visible = $fromform->visible;
        $recording->timemodified = time();
        $success = false;
        if($item) {
            // we are updating
            $recording->id = $item;
            $success = $DB->update_record('teamsmeeting_recording', $recording);
        } else {
            //we are creating a new one
            unset($recording->id);
            $recording->timecreated = $recording->timemodified;
            $success = $DB->insert_record('teamsmeeting_recording', $recording);
        }
        
        if($success) {
            $message = 'recordingsaved';
            $type = \core\output\notification::NOTIFY_SUCCESS;
                                                
        } else {
            $message = 'recordingnotsaved';
            $type = \core\output\notification::NOTIFY_ERROR;
        }
        
        \core\notification::add(get_string('recordingsaved', 'teamsmeeting', $recording->name), $type);
        redirect($baseurl);
    }
    
/// Print the form
echo $OUTPUT->header();

echo $OUTPUT->heading_with_help($straction, 'recording', 'teamsmeeting');
$mform ->display();   
    
echo $OUTPUT->footer();    
