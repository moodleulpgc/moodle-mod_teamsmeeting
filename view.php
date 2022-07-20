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
 * Prints an instance of mod_teamsmeeting.
 *
 * @package     mod_teamsmeeting
 * @copyright   2020 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/locallib.php');

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
if($groupid) {
    $urlparams['group'] = $groupid;
}

$baseurl = new moodle_url('/mod/teamsmeeting/view.php', $urlparams);

$PAGE->set_url($baseurl);
$PAGE->set_title(format_string($teamsmeeting->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_activity_record($teamsmeeting);

$cancreate = is_enrolled($context, null, 'mod/teamsmeeting:create', true);
$canjoin = is_enrolled($context, null, 'mod/teamsmeeting:join', true);

/// proccess actions
if($cancreate) {
    if($update = optional_param('update', 0, PARAM_INT)) { 
        teamsmeeting_update_onlinemeeting($teamsmeeting, $update);
    }
    if($create = optional_param('create', 0, PARAM_INT)) {
        teamsmeeting_create_onlinemeeting($teamsmeeting, $groupid);    
    }
}

teamsmeeting_view($teamsmeeting, $course, $cm, $context);

$renderer = $PAGE->get_renderer('mod_teamsmeeting');
echo $renderer->header();
echo $renderer->heading(format_string($teamsmeeting->name), 2, null);

if ($teamsmeeting->intro) {
    echo $OUTPUT->box(format_module_intro('teamsmeeting', $teamsmeeting, $cm->id), 'generalbox', 'intro');
}

$forgroupname = '';

if ($groupmode) {
    // Separate or visible group mode.
    $groups = groups_get_activity_allowed_groups($cm);
    if(count($groups) == 0) {
        \core\notification::add(get_string('groupsnoone', 'teamsmeeting'), 
                                    \core\output\notification::NOTIFY_WARNING);
    } elseif(count($groups) > 1) {
        \core\notification::add(get_string('groupsmultiple', 'teamsmeeting'), 
                                    \core\output\notification::NOTIFY_WARNING);
    }
    
    groups_print_activity_menu($cm, $baseurl);
    if($cancreate) {
        //echo $renderer->show_manage_overrides_link($cm->id);
    }

    

    $forgroupname =  get_string('allparticipants');
    if($groupid) {  
        // if groupmode && user is not member of group then cannot join
        if (!(groups_is_member($groupid) || has_capability('moodle/site:accessallgroups', $context))) {
            $canjoin = false;
        }
        
        $forgroupname = groups_get_group_name($groupid); 
    } elseif($groupmode == SEPARATEGROUPS) {
        \core\notification::add(get_string('groupsnotvisible', 'teamsmeeting'), 
                                    \core\output\notification::NOTIFY_WARNING);
    }
    
    $forgroupname = \html_writer::span(get_string('forgroup', 'teamsmeeting', $forgroupname), ' forgroup ');
}

//print_object("join: $canjoin || create: $cancreate ");

if($canjoin || $cancreate) {
    // when accessed by teacher, resets waiting
    
    $onlinemeeting = teamsmeeting_get_onlinemeeting($teamsmeeting, $cancreate, $groupid);
    //print_object($onlinemeeting);
    
    echo $renderer->show_dates($onlinemeeting);  
    
    $output = '';
    if($onlinemeeting->access == 'join') {
        $output = $renderer->show_join_button($onlinemeeting, $forgroupname, $cancreate);
        if ($cancreate && ($onlinemeeting->status == OLMEETING_STATE_UPDATE)) {
            $output = $renderer->show_create_update_button($onlinemeeting, $forgroupname);
        }
    } elseif ($onlinemeeting->access == 'waiting') {        
        $output = $renderer->box(get_string('waitformoderator', 'teamsmeeting'), 'box centerpara  alert-warning text-danger');
    } elseif ($onlinemeeting->access == 'pastdue') {        
        $output = $renderer->box(get_string('waitpastdue', 'teamsmeeting'), 'box centerpara  alert-warning text-danger');
    } else {
        if ($onlinemeeting->access == 'none') {        
            $output = $renderer->box(get_string('waitforcreation', 'teamsmeeting'), 'box centerpara  alert-warning text-danger');
        }    
        if ($cancreate && (!$onlinemeeting->closingtime || $teamsmeeting->useafterwards || 
                    ($onlinemeeting->closingtime && ($onlinemeeting->closingtime > time()))) ) {
            $output = $renderer->show_create_update_button($onlinemeeting, $forgroupname);
        }
    }
    
    echo $output;
    
} else {
    echo $renderer->box(get_string('nothingtosee', 'teamsmeeting'), 'box centerpara  alert-warning text-danger');
}

    echo $renderer->container('', 'separator clearfix');

// now thee recordings table, if used
if($teamsmeeting->showrecordings && isset($onlinemeeting->id)) {
    $canmanagerecs = is_enrolled($context, null, 'mod/teamsmeeting:managerecordings', true);
    $renderer->show_recordings_table($onlinemeeting, $canmanagerecs);
    
    if($canmanagerecs) {
        echo $renderer->add_recording_button($onlinemeeting, $forgroupname);
    }
}

$back = new moodle_url('/course/view.php', array('id' => $course->id));
echo $renderer->single_button($back, get_string('backtocourse', 'teamsmeeting'), 'get', array('class' => 'backtocourse clearfix'));

echo $renderer->footer();
