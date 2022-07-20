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
 * Prints & manage group overrides of mod_teamsmeeting.
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
    print_error(get_string('missingidandcmid', 'teamsmeeting'));
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

// Check the user has the required capabilities to list overrides.
require_capability('mod/teamsmeeting:create', $context);

// set url params 
$urlparams = array('id' => $cm->id);
/// Check to see if groups are being used in this examboard
$groupmode = groups_get_activity_groupmode($cm);
$accessallgroups = ($groupmode == NOGROUPS) || has_capability('moodle/site:accessallgroups', $context);
// Get the course groups that the current user can access.
$groups = $accessallgroups ? groups_get_all_groups($cm->course. null, $cm->groupingid) : groups_get_activity_allowed_groups($cm);
$groupid = 0;
if ($groupmode) {
    $groupid = groups_get_activity_group($cm, true);
}
if($groupid) {
    $urlparams['group'] = $groupid;
}

$baseurl = new moodle_url('/mod/teamsmeeting/overrides.php', $urlparams);

$PAGE->set_url($baseurl);
$PAGE->set_title(format_string($teamsmeeting->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_activity_record($teamsmeeting);
$PAGE->navbar->add(get_string('overrideslink', 'teamsmeeting'), $baseurl);

$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$item = optional_param('item', 0, PARAM_INT);
$reset = optional_param('reset', '', PARAM_ALPHA);

$override = $DB->get_record('teamsmeeting_overrides', array('id' => $item));
$used = $DB->get_records_menu('teamsmeeting_overrides', array('teamsmeetingid' => $teamsmeeting->id), null, 'id, groupid');

$mform = false;

if($action == 'del' && $item) {
    if($DB->delete_records('teamsmeeting_overrides', array('id' => $item))) {
        $groupname = $groups[$override->groupid]->name;
        \core\notification::add(get_string('overridedeleted', 'teamsmeeting', $groupname), \core\output\notification::NOTIFY_SUCCESS);                
    } else {
        \core\notification::add(get_string('overrideerror', 'teamsmeeting', $groupname), \core\output\notification::NOTIFY_ERROR);                
    }
    //check if there are onlinemeetings with onlinemeeting code
    if($onlinemeeting = $DB->get_record('teamsmeeting_onlinemeeting', 
                array('teamsmeetingid' => $teamsmeeting->id, 'groupid' => $override->groupid, 'expired' => 0))) {
        //there are onlinemeetings, we must connect with ISL to delete
        $url = new moodleurl('/mod/teamsmeeting/islconnect.php', 
                        array('id'=>$cm->id, 'action' => 'delonlinemeeting', 'item' => $onlinemeeting->id));
        redirect($url);
    }

} elseif( $action == 'add' || ($action == 'edit' && $item)) {

    $selgroups = $groups;
    if($action == 'add') {
        //we are adding, avoid enter duplicate group exceptions
        //just unset used groupids
        $selgroups = array_diff_key($groups, array_flip($used));
    }

    $default = $item ? $override : $teamsmeeting;
    // to be able to restore defaults on form reset
    $default = $reset ? $teamsmeeting : $default;
    if($item && $override) {
        $default->groupid = $override->groupid;
    }
    
    if($action == 'add') {
        // just  for security
        $item = false;
    }
    
    $mform = new \mod_teamsmeeting\form\override_form(null, 
                    array('item'=> $item, 'groups'=> $selgroups, 'default' => $default));
    
    if ($mform->is_cancelled()) {
        $mform = false;
    } else if ($fromform = $mform->get_data()) {
        // OK, let's proceed to add or update
        
        if(isset($fromform->resetbutton) && $fromform->resetbutton) {
            $baseurl->param('action', $action);
            $baseurl->param('item', $item);
            $baseurl->param('reset', 'true');
            redirect($baseurl);
        }
        
        $record = new stdClass();
        $record->teamsmeetingid = $teamsmeeting->id;
        $record->groupid = $fromform->groupid;
        $record->openingtime = $fromform->openingtime;
        $record->closingtime = $fromform->closingtime;
        $record->notifytime = $fromform->notifytime;
        $record->timemodified = time();
        
        $groupname = $groups[$record->groupid]->name;
        
        $success = false;
        if($action == 'edit' && $item && $override) {
            $record->id = $override->id;
            if($success = $DB->update_record('teamsmeeting_overrides', $record)) {
                \core\notification::add(get_string('overrideupdated', 'teamsmeeting', $groupname), \core\output\notification::NOTIFY_SUCCESS);                
            }
        } else {
            if($DB->record_exists('teamsmeeting_overrides', 
                            array('teamsmeetingid' => $teamsmeeting->id, 'groupid' => $record->groupid))) {
                \core\notification::add(get_string('overrideexisting', 'teamsmeeting', $groupname), \core\output\notification::NOTIFY_ERROR);                                            
            } else {
                if($record->id = $DB->insert_record('teamsmeeting_overrides', $record)) {
                    $success = true;
                    \core\notification::add(get_string('overrideadded', 'teamsmeeting', $groupname), \core\output\notification::NOTIFY_SUCCESS);                
                }
            }
        }
        if(!$success) {
            \core\notification::add(get_string('overrideerror', 'teamsmeeting', $groupname), \core\output\notification::NOTIFY_ERROR);                
        }
        if(isset($fromform->againbutton) && $fromform->againbutton) {
            $baseurl->param('action', 'add');
            redirect($baseurl);
        }
        $mform = false;
    }
}


////Print the page

$renderer = $PAGE->get_renderer('mod_teamsmeeting');

echo $renderer->header();
echo $renderer->heading(format_string($teamsmeeting->name), 2, null);

if($mform) {
    // there is a form, just display it
    $mform->display();

} else {
    //if not a form, show the overrides table

    // find existing overrrides
    $params = array();
    $inusergroups = '';
    if(!$accessallgroups) {
        $usergroups = groups_get_activity_allowed_groups($cm); 
        if($usergroups) {
            list($insql, $params) = $DB->get_in_or_equal(array_keys($usergroups), SQL_PARAMS_NAMED);
            $inusergroups = " AND o.groupid $insql ";
        }
    }

    $hasexpired = false;
    $expired = ' AND (s.expired = 0 OR s.expired IS NULL ) ';
    if($withex = optional_param('withex', '', PARAM_ALPHANUMEXT)) {
        $expired = '';
    }

    $usernames = get_all_user_name_fields(true, 'u');
    $sql = "SELECT o.*, g.name, s.id AS onlinemeetingid, s.teacherid, s.joinurl, u.idnumber, $usernames
            FROM {teamsmeeting_overrides} o
            JOIN {groups} g ON g.id = o.groupid
            LEFT JOIN {teamsmeeting_onlinemeeting} s ON s.teamsmeetingid = o.teamsmeetingid AND s.groupid = o.groupid
            LEFT JOIN {user} u ON s.teacherid = u.id
            WHERE o.teamsmeetingid = :teamsmeetingid $inusergroups $expired 
            ORDER BY g.name ";
    $params['teamsmeetingid'] = $teamsmeeting->id;

    $groupoverrides = $DB->get_records_sql($sql, $params);

    //print_object($groupoverrides); 
    //print_object(" ------------ groupoverrides -------------");

    //$groupoverrides = teamsmeeting_get_overrides_with_onlinemeetings($teamsmeeting->id, $cm, $accessallgroups);
        
    // Initialise table.
    $table = new html_table();
    $table->colclasses = array('colname', 'colvalue', 'colvalue', 'colvalue', 'colvalue', 'colvalue', 'colaction');
    $table->head = array(   get_string('group'),
                            get_string('openingtime', 'teamsmeeting'),
                            get_string('closingtime', 'teamsmeeting'),
                            get_string('notifytime', 'teamsmeeting'),
                            get_string('teacher', 'teamsmeeting'),
                            get_string('joinurllink', 'teamsmeeting'),
                            get_string('action'),
                        );

    $used = array();
    $groupurl = new moodle_url('/user/index.php', array('id' => $course->id));
    $userurl = new moodle_url('/user/view.php', array('course' => $course->id));
    $regurl = new moodle_url('/mod/teamsmeeting/registrations.php', array('id' => $cm->id));
    $islurl = get_config('teamsmeeting', 'islserverurl');
    if(substr($islurl, -1) != '/') {
        $islurl .=  '/';
    }
    $islurl .= 'join/';
    
    foreach ($groupoverrides as $override) {
        $row = new html_table_row();
        
        $cell = new html_table_cell();
        $groupurl->param('group', $override->groupid);
        $cell->text = html_writer::link($groupurl, format_string($override->name));
        $row->cells[] = clone $cell;
        
        $cell->text = ($override->openingtime) ? userdate($override->openingtime) : '-';
        $row->cells[] = clone $cell;
        $cell->text = ($override->closingtime) ? userdate($override->closingtime) : '-';
        $row->cells[] = clone $cell;
        $cell->text = ($override->notifytime) ? format_time($override->notifytime) : '-';
        $row->cells[] = clone $cell;
        
        $userurl->param('id', $override->teacherid);
        $cell->text = (isset($override->teacherid) && $override->teacherid) ? html_writer::link($userurl, fullname($override)) : '-';
        $row->cells[] = clone $cell;
        
        $cell->text = (isset($override->joinurl)) ? html_writer::link($override->joinurl, get_string('joinbutton','teamsmeeting')) : '-';
        $row->cells[] = clone $cell;
        
        // Icons.
        $iconstr = '';

        // Edit.
        $baseurl->param('item', $override->id);
        $baseurl->param('action', 'edit');
        $icon = new pix_icon('t/edit', get_string('edit'), 'core', array());
        $iconstr =  $OUTPUT->action_icon($baseurl, $icon);
        $iconstr .= $OUTPUT->spacer();                
        
        // Delete.
        $baseurl->param('action', 'del');
        $confirmaction = new \confirm_action(get_string('overridedelconfirm', 'teamsmeeting', $override->name));
        $icon = new pix_icon('t/delete', get_string('delete'), 'core', array());
        $iconstr .=  $OUTPUT->action_icon($baseurl, $icon, $confirmaction);
                    
        $cell->text = $iconstr;
        $row->cells[] = clone $cell;                
        
        $table->data[] = $row;
        $used[$override->id] = $override->groupid;
    }

    // Output the table and button.
    echo html_writer::start_tag('div', array('id' => 'overrides'));
    if (count($table->data)) {
        echo html_writer::table($table);
    } else {
        echo $renderer->box(get_string('overridenot', 'teamsmeeting'), 'box centerpara  alert-warning');
    }
    if ($hasexpired) {
        echo $OUTPUT->notification(get_string('overrideinactivehelp', 'teamsmeeting'), 'dimmed_text');
    }

    echo html_writer::start_tag('div', array('class' => 'buttons'));

        $options = array();
        $baseurl->param('action', 'add');
        $baseurl->remove_params('item');

        // remove used groupid to check if adding new group overrides 
        $groups = array_diff_key($groups, array_flip($used));
        if (empty($groups)) {
            // There are no groups.
            echo $OUTPUT->notification(get_string('groupsnone', 'teamsmeeting'), 'error');
            $options['disabled'] = true;
        }    
        echo $OUTPUT->single_button($baseurl,
                get_string('overrideaddnew', 'teamsmeeting'), 'post', $options);

    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');

}

echo $renderer->footer();
