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
 * All the steps to restore mod_teamsmeeting are defined here.
 *
 * @package     mod_teamsmeeting
 * @category    restore
 * @copyright   2020 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For more information about the backup and restore process, please visit:
// https://docs.moodle.org/dev/Backup_2.0_for_developers
// https://docs.moodle.org/dev/Restore_2.0_for_developers

/**
 * Defines the structure step to restore one mod_teamsmeeting activity.
 */
class restore_teamsmeeting_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines the structure to be restored.
     *
     * @return restore_path_element[].
     */
    protected function define_structure() {
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');
        $groupinfo = $this->setting_exists('groups') ? $this->get_setting_value('groups') : '';

        $paths[] = new restore_path_element('teamsmeeting', '/activity/teamsmeeting');
        
        if($groupinfo) {
            $paths[] = new restore_path_element('teamsmeeting_override', '/activity/teamsmeeting/overrides/override');
            $paths[] = new restore_path_element('teamsmeeting_recording', '/activity/teamsmeeting/recordings/recording');
        }
        
        if($groupinfo && $userinfo) {
            $paths[] = new restore_path_element('teamsmeeting_onlinemeeting', '/activity/teamsmeeting/onlinemeetings/onlinemeeting');
        }
        
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Processes the teamsmeeting restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_teamsmeeting($data) {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        
        $data->openingtime = $this->apply_date_offset($data->closingtime);
        $data->closingtime = $this->apply_date_offset($data->closingtime);
        
        // insert the record
        $newitemid = $DB->insert_record('teamsmeeting', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Processes the override restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_teamsmeeting_override($data) {
        global $DB;
        
        $data = (object)$data;

        $data->teamsmeetingid = $this->get_new_parentid('teamsmeeting');
        
        $data->openingtime = $this->apply_date_offset($data->closingtime);
        $data->closingtime = $this->apply_date_offset($data->closingtime);

        if (!empty($data->groupid)) {
            $data->groupid = $this->get_mappingid('group', $data->groupid);
        } else {
            $data->groupid = 0;
        }
 
        $newitemid = $DB->insert_record('teamsmeeting_overrides', $data);
    }

    
    
    /**
     * Processes the recording restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_teamsmeeting_onlinemeeting($data) {
        global $DB;
    
        $data = (object)$data;
        
        $data->teamsmeetingid = $this->get_new_parentid('teamsmeeting');
        $data->teacherid = $this->get_mappingid('user', $data->teacherid);
        if (!empty($data->groupid)) {
            $data->groupid = $this->get_mappingid('group', $data->groupid);
        } else {
            $data->groupid = 0;
        }
 
        $newitemid = $DB->insert_record('teamsmeeting_onlinemeeting', $data);
    }

    
    /**
     * Processes the recording restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_teamsmeeting_recording($data) {
        global $DB;
        $data = (object)$data;
        
        $data->onlinemeetingid = $this->get_new_parentid('onlinemeeting');
        
 
        $newitemid = $DB->insert_record('teamsmeeting_recording', $data);
    }

    /**
     * Defines post-execution actions.
     */
    protected function after_execute() {
        $this->add_related_files('mod_teamsmeeting', 'intro', null);
    }
}
