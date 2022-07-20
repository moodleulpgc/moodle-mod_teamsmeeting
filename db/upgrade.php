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
 * This file keeps track of upgrades to the teamsmeeting module
 *
 * @package    mod_teamsmeeting
 * @copyright  2021 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute teamsmeeting upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_teamsmeeting_upgrade($oldversion) {
    global $DB, $USER;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2021040502) {
        $table = new xmldb_table('teamsmeeting');
        $field = new xmldb_field('membership', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'closingtime');
        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('allowedpresenters', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null, 'membership');        
        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('lobbybypass', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null, 'allowedpresenters');        
        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('joinannounce', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'lobbybypass');
        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('authtype', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'joinannounce');        
        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('externalurl', XMLDB_TYPE_TEXT, null, null, null, null, null, 'authtype');        
        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2021040502, 'teamsmeeting');
    }
    
    if ($oldversion < 2021051500) {
        $table = new xmldb_table('teamsmeeting_onlinemeeting');
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'joinurl');
        // Conditionally launch add field status.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        $field = new xmldb_field('endtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'status');
        // Conditionally launch add field endtime.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2021051500, 'teamsmeeting');
    }    
    
    if ($oldversion < 2021051505) {
        $table = new xmldb_table('teamsmeeting_onlinemeeting');        $table = new xmldb_table('teamsmeeting_onlinemeeting');

        $field = new xmldb_field('chatinfotid', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'joinurl');        
        // Conditionally launch add field chatthreadid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        // Define table groupdate to be renamed to overrides.
        $table = new xmldb_table('teamsmeeting_groupdate');
        // Be careful if this step gets run twice.
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('notifytime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'closingtime');        
            // Conditionally launch add field chatthreadid.
            if (!$dbman->field_exists($table, $field)) {        $table = new xmldb_table('teamsmeeting_onlinemeeting');

                $dbman->add_field($table, $field);
            }
            
            // Launch rename table for overrides.
            $dbman->rename_table($table, 'teamsmeeting_overrides');
        
        }    
    
        $table = new xmldb_table('teamsmeeting_recording');
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('teamsmeetingid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);        
            if($dbman->field_exists($table, $field)) {
                $dbman->drop_table($table);
            }
 
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null );
            $table->add_field('onlinemeetingid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null ); 
            $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null );         
            $table->add_field('streamurl', XMLDB_TYPE_TEXT, null, null, null, null, null );        
            $table->add_field('visible', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0 );
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null ); 
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
            
            // Adding keys to table 
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('meetingid', XMLDB_KEY_FOREIGN, array('onlinemeetingid'), 'teamsmeeting_onlinemeeting', array('id'));
            
            if (!$dbman->table_exists($table)) {
                $dbman->create_table($table);
            }            
        }        
    
        upgrade_mod_savepoint(true, 2021051505, 'teamsmeeting');
    }    

    
    if ($oldversion < 2021051506) {    
        $table = new xmldb_table('teamsmeeting');
        $field = new xmldb_field('wait', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_precision($table, $field);
        }
    
    }
    
    
    if ($oldversion < 0) {
    
        $table = new xmldb_table('teamsmeeting_recording');
        $field = new xmldb_field('dischargeformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'name');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('dischargetext', XMLDB_TYPE_TEXT, null, null, null, null, null, 'name');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        upgrade_mod_savepoint(true, 2021051505, 'teamsmeeting');
    }       
    
    

    
    return true;
}
