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
 * Backup steps for mod_teamsmeeting are defined here.
 *
 * @package     mod_teamsmeeting
 * @category    backup
 * @copyright   2020 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For more information about the backup and restore process, please visit:
// https://docs.moodle.org/dev/Backup_2.0_for_developers
// https://docs.moodle.org/dev/Restore_2.0_for_developers

/**
 * Define the complete structure for backup, with file and id annotations.
 */
class backup_teamsmeeting_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the structure of the resulting xml file.
     *
     * @return backup_nested_element The structure wrapped by the common 'activity' element.
     */
    protected function define_structure() {
        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // To know if we are including groups and groupings.
        $groupinfo = $this->setting_exists('groups') ? $this->get_setting_value('groups') : '';

        $final_elements = array('name', 
                                'intro',
                                'introformat',
                                'openingtime', 
                                'closingtime',
                                'membership',
                                'allowedpresenters',
                                'lobbybypass',
                                'joinannounce',
                                'authtype',
                                'showrecordings',
                                'wait',
                                'useafterwards',
                                'notifytime',
                                'timecreated',
                                'timemodified',
        );
        $teamsmeeting = new backup_nested_element('teamsmeeting', array('id'), $final_elements);

        $overrides = new backup_nested_element('overrides');
        $final_elements = array('groupid', 
                                'openingtime', 
                                'closingtime',
                                'notifytime',
                                'timemodified',        
        );
        $override = new backup_nested_element('override', array('id'), $final_elements);
        
        $onlinemeetings = new backup_nested_element('onlinemeetings');
        $final_elements = array('groupid', 
                                'teacherid', 
                                'timeactivated',
                                'meetingid',
                                'externalid',
                                'vtcid',
                                'joinurl',
                                'chatinfotid',
                                'status',
                                'endtime',
                                'expired',
                                'timecreated',
                                'timemodified',
        );
        $onlinemeeting = new backup_nested_element('onlinemeeting', array('id'), $final_elements);
        
        
        $recordings = new backup_nested_element('recordings');
        $final_elements = array('onlinemeetingname',
                                'streamurl',
                                'visible',
                                'timecreated',
                                'timemodified',
                                
        );
        $recording = new backup_nested_element('recording', array('id'), $final_elements);
        
        // Build the tree.
        $teamsmeeting->add_child($overrides);
        $overrides->add_child($override);
        
        $teamsmeeting->add_child($onlinemeetings);
        $onlinemeetings->add_child($onlinemeeting);
        
        $onlinemeetings->add_child($recordings);
        $recordings->add_child($recording);

        // Define the source tables for the elements.
        $teamsmeeting->set_source_table('teamsmeeting', array('id' => backup::VAR_ACTIVITYID));
        
        if ($groupinfo) {
            $override->set_source_table('teamsmeeting_overrides', array('teamsmeetingid' => backup::VAR_PARENTID), 'id ASC');
            $recording->set_source_table('teamsmeeting_recording', array('onlinemeetingid' => backup::VAR_PARENTID), 'id ASC');
        }

        if($groupinfo && $userinfo) {
            $onlinemeeting->set_source_table('teamsmeeting_onlinemeeting', array('teamsmeetingid' => backup::VAR_PARENTID), 'id ASC');        
        }
        
        // Define id annotations.
        $override->annotate_ids('group', 'groupid');
        
        $onlinemeeting->annotate_ids('group', 'groupid');
        $onlinemeeting->annotate_ids('user', 'teacherid');
        
        // Define file annotations.
        $teamsmeeting->annotate_files('mod_teamsmeeting', 'intro', null);
        
        return $this->prepare_activity_structure($teamsmeeting);
    }
}
