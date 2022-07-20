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
 * Plugin administration pages are defined here.
 *
 * @package     mod_teamsmeeting
 * @category    admin
 * @copyright   2020 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
   // TODO: Define the plugin settings page.
   // https://docs.moodle.org/dev/Admin_settings

    $settings->add(new admin_setting_heading('teamsmeeting_access_mode', get_string('o365accessmode', 'teamsmeeting'), 
                        get_string('o365accessmode_desc', 'teamsmeeting')));
   /* 
    $settings->add(new admin_setting_configcheckbox('teamsmeeting/accessasuser', get_string('accessasuser', 'teamsmeeting'), 
                        get_string('accessasuser_desc', 'teamsmeeting'), 0));

    $methods = [0 => new lang_string('accessasuser', 'teamsmeeting'), 
                1 => new lang_string('apponbehalf', 'teamsmeeting'),
                2 => new lang_string('appsystemuser', 'teamsmeeting'), ]; 
                */
    $methods = [0 => new lang_string('accessasuser', 'teamsmeeting'), 
                1 => 'apponbehalf',
                2 => 'appsystemuser', ]; 
                
    $settings->add(new admin_setting_configselect('teamsmeeting/accessmethod', get_string('accessasuser', 'teamsmeeting'), 
                        get_string('accessasuser_desc', 'teamsmeeting'), 0, $methods));

                        
    $settings->add(new admin_setting_configtext('teamsmeeting/organizerid', get_string('organizeruser', 'teamsmeeting'), 
                        get_string('organizeruser_desc', 'teamsmeeting'), '', PARAM_TEXT));   
    $settings->hide_if('teamsmeeting/organizerid', 'teamsmeeting/accessmethod', 'neq', 2);

    $settings->add(new admin_setting_configcheckbox('teamsmeeting/updateremote', get_string('updateremote', 'teamsmeeting'), 
                        get_string('updateremote_desc', 'teamsmeeting'), 0));
    
    $settings->add(new admin_setting_configcheckbox('teamsmeeting/deleteremote', get_string('deleteremote', 'teamsmeeting'), 
                        get_string('deleteremote_desc', 'teamsmeeting'), 0));
    
    $settings->add(new admin_setting_heading('teamsmeeting_operation_settings', 
                        get_string('operationsettings', 'teamsmeeting'), ''));    
    $settings->add(new admin_setting_configduration('teamsmeeting/teacheradvance', get_string('teacheradvance', 'teamsmeeting'),
                        get_string('teacheradvance_desc', 'teamsmeeting'), 7200, 3600));

   
    $settings->add(new admin_setting_configtext('teamsmeeting/expirationdays', get_string('expirationdays', 'teamsmeeting'),
                        get_string('expirationdays_desc', 'teamsmeeting'), 60, PARAM_INT, 4));
                       
    $settings->add(new admin_setting_configcheckbox('teamsmeeting/showrecordings', get_string('showrecordings', 'teamsmeeting'), 
                        get_string('showrecordings_desc', 'teamsmeeting'), 0));
}
