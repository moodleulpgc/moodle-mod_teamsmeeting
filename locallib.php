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
 * Library of mod_teamsmeeting .
 *
 * @package     mod_teamsmeeting
 * @copyright   2020 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();



/**
 * checks dependent onlinemeetings for updating if settings are changed
 *
 * @param object $teamsmeeting An object from the form in mod_form.php.
 * @param mod_teamsmeeting_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function teamsmeeting_checkupdating_onlinemeetings($teamsmeeting, $old) {
    global $DB;
    
    $fields = [ 'name' => 'subject', 
                'openingtime' => 'startDateTime', 
                'closingtime' => 'endDateTime', 
                'membership' => 'membership', 
                'allowedpresenters' => 'allowedPresenters', 
                'lobbybypass' =>' lobbyBypassSettings', 
                'joinannounce' => 'isEntryExitAnnounced'];
                
    foreach($fields as $key => $field) {
        if($teamsmeeting->$key == $old->$key) {
            unset($fields[$key]);
        }
    }
    
    // ensure both opening & closing are set if any of them. required by o369 Graph API
    if(!empty($fields)) {
        if(array_key_exists('openingtime', $fields)) {
            $fields['closingtime'] = 'endDateTime';
        } 
        if(array_key_exists('closingtime', $fields)) {
            $fields['openingtime'] = 'startDateTime';
        } 
    }
    
    return $fields;
}

/**
 * Check & update appropiate dates for each group
 *
 * @param stdClass $teamsmeeting
 * @param int $groupid
 *
 * @return stdClass
 */
function teamsmeeting_update_group_dates($teamsmeeting, $groupid) {
    global $DB;

    $params = array('teamsmeetingid' => $teamsmeeting->id,
                    'groupid' => $groupid);
    $override = $DB->get_record('teamsmeeting_overrides', $params);

    if($override) {
        $teamsmeeting->openingtime = $overrides->openingtime;
        $teamsmeeting->closingtime = $overrides->closingtime;
    }

    return $teamsmeeting;
}



/**
 * Gets an onlinemeeting object form DB and add status
 * When accesed by a teacher, resets the waiting  mark time
 *
 * @param stdClass $teamsmeeting
 * @param bool $cancreate
 * @param int $groupid
 *
 * @return stdClass
 */
function teamsmeeting_get_onlinemeeting($teamsmeeting, $cancreate, $groupid) {
    global $DB;

    $now = time();
    if($groupid) {
        $teamsmeeting = teamsmeeting_update_group_dates($teamsmeeting, $groupid);
    }

    $onlinemeeting = teamsmeeting_get_valid_onlinemeeting($teamsmeeting, $groupid, $cancreate);

    //print_object("inicial. Access = ".$onlinemeeting->access );
    
    
    $joiningtime = $teamsmeeting->openingtime;
    if($cancreate) {
        $teacheradvance = get_config('teamsmeeting',  'teacheradvance');
        $joiningtime = $teamsmeeting->openingtime - (int)$teacheradvance;
    }
    
    // checkdates
    if(($teamsmeeting->openingtime && ($now < $joiningtime)) ||
        ($teamsmeeting->closingtime && (($now > $teamsmeeting->closingtime) && $teamsmeeting->useafterwards))) {
        $after = ($teamsmeeting->useafterwards && $cancreate) ? 'none' : '';
        $onlinemeeting->access = ($onlinemeeting->meetingid) ? 'out' : $after;
        //print_object("En check dates Access= ".$onlinemeeting->access);
        
        return $onlinemeeting;
    }

    if(!$onlinemeeting->meetingid) {
        $onlinemeeting->access = $cancreate ? '' : 'none'; 
        
        //print_object("En meetingid Access= ".$onlinemeeting->access);
        
        return  $onlinemeeting;
    }        
    
    if($cancreate) {
        $teacheradvance = get_config('teamsmeeting',  'teacheradvance');
        if($now > $joiningtime) {
            $onlinemeeting->timeactivated = $now;
        }
    }

    if($teamsmeeting->wait) {
        $onlinemeeting->access = 'waiting';
        if($onlinemeeting->timeactivated && 
            ($now >= $onlinemeeting->timeactivated)){
                $onlinemeeting->access = 'join';
                if($now > $onlinemeeting->timeactivated + $teamsmeeting->wait) {
                    $onlinemeeting->access = 'pastdue';
                }
            }
    } else {
        $onlinemeeting->access = 'join';
    }
    
    if($teamsmeeting->externalurl) {
        $existingdata = teamsmeeting_get_onlinemeeting_settings($teamsmeeting, $teamsmeeting->externalurl);
        //print_object($existingdata);
    }
    
    //print_object($onlinemeeting);

    return $onlinemeeting;
}


/**
 * Collects the recordings available for this meeting & user
 *
 * @param stdClass $onlinemeeting
 * @param bool $canmanage
 *
 * @return array 
 */
function teamsmeeting_get_recordings($onlinemeeting, $canmanage = false) {
    global $DB;
    
    $recordings = [];
    
    $params = ['onlinemeetingid' => $onlinemeeting->id];
    if(!$canmanage) {
        $params['visible'] = 1;
    }
    $recordings = $DB->get_records('teamsmeeting_recording', $params);
    

    return $recordings;
}


/**
 * Build a void onlinemeeting object without functional o365 properties
 *
 * @param int $teamsmeeting module instance record
 * @param int $groupid
 *
 * @return stdClass
 */
function teamsmeeting_empty_onlinemeeting($teamsmeeting, $groupid) {
    $onlinemeeting = new stdClass();
    $onlinemeeting->id = null;
    $onlinemeeting->teamsmeetingid = $teamsmeeting->id;
    $onlinemeeting->groupid = $groupid;
    $onlinemeeting->teacherid = 0;
    $onlinemeeting->meetingid = '';
    $onlinemeeting->joinurl = '';
    if($teamsmeeting->externalurl) {
        $onlinemeeting->joinurl = $teamsmeeting->externalurl;
    }
    $onlinemeeting->access = '';
    $onlinemeeting->status = '';
    $onlinemeeting->timeactivated = 0;

    return $onlinemeeting;
}

/**
 * Creates an onlinemeeting in o365 and sets a record in DB
 *
 * @param int $teamsmeetingid ID for this instance
 * @param int $groupid
 *
 * @return stdClass
 */
function teamsmeeting_create_onlinemeeting($teamsmeeting, $groupid) {
    global $DB, $USER;

    if($groupid) {
        $teamsmeeting = teamsmeeting_update_group_dates($teamsmeeting, $groupid);
    }

    $o365api = \mod_teamsmeeting\rest\unified::get_o365api($teamsmeeting);            

    if(0 || ($o365api && $o365api->is_working())) {
        //$tokenallowed = $o365api->checktoken_valid($userid);
        //$tokenallowed = true;
        if($o365api->tokenallowed) {
            $onlinemeeting = $o365api->create_onlinemeeting($teamsmeeting, $groupid);
        } else {
        
        }
    }
}


/**
 * Updates an onlinemeeting in o365 
 *
 * @param object $teamsmeeting instance record adjusted for overrides times
 * @param object $onlinemeeting record from DB
 * @param array $update the moodle ID of the onlinemeeting to update
 *
 * @return stdClass
 */
function teamsmeeting_update_onlinemeeting($teamsmeeting, $onlinemeeting, $update = false) {
    global $DB, $USER;
    
    $o365api = \mod_teamsmeeting\rest\unified::get_o365api($teamsmeeting, $onlinemeeting->teacherid); 
    if(0 || ($o365api && $o365api->is_working())) {
        if($o365api->tokenallowed) {
            //print_object("proceed to update");
            $o365meeting = $o365api->get_onlinemeeting($onlinemeeting->meetingid);
            
            // only update if required
            $updatedata = $o365api->build_common_meetingdata($teamsmeeting);
            if(!empty($update)) {
                foreach($updatedata as $key => $value) {
                    if(!in_array($key, $update)) {
                        unset($updatedata[$key]);
                    }
                }
            }
            
            if($onlinemeeting->groupid) {
                $params = ['type' => 'group', 'subtype' => 'teamchannel', 'moodleid' => $onlinemeeting->groupid];
                if($channelrec = $DB->get_record('local_o365_objects', $params)) {
                    $updatedata['chatInfo']['threadId']  = $channelrec->objectid;
                }
            }
            
            if($attendees = $o365api->load_presenters($teamsmeeting, $onlinemeeting->groupid)) {
                $updatedata['participants'] = ['attendees' => array_filter(array_values($attendees))];
            }
            //print_object($updatedata);
            //print_object("  structure for updating data ");
            
            $o365meeting = $o365api->update_onlinemeeting($onlinemeeting->meetingid, $updatedata);
            if(!empty($o365meeting)) {
                $onlinemeeting->status = OLMEETING_STATE_OK;
                $onlinemeeting->timemodified= time();
                $DB->update_record('teamsmeeting_onlinemeeting', $onlinemeeting);
            }
        } else {
        
        }
    }    
    
    
}


/**
 * Gets or creates an onlinemeeting object from DB
 * When accesed by a teacher, resets the waiting  mark time
 *
 * @param int $teamsmeetingid ID for this instance
 * @param int $groupid
 * @param bool $create if the meeting will be created if not existing
 *
 * @return stdClass
 */
function teamsmeeting_get_valid_onlinemeeting($teamsmeeting, $groupid, $create = false) {
    global $DB, $USER;

    //print_object("entrando en teamsmeeting_get_valid_onlinemeeting");
    
    $now = time();
    $params = array('teamsmeetingid' => $teamsmeeting->id, 'groupid' => $groupid , 'expired' => 0 );
    $onlinemeeting = $DB->get_record('teamsmeeting_onlinemeeting', $params);
    
    
    if(!$onlinemeeting) {
        // create a new empty one
        $onlinemeeting = teamsmeeting_empty_onlinemeeting($teamsmeeting, $groupid);
    }    
    
    
    //print_object("en get valis CREATE = $create");
    
    if($create) {
        $o365api = \mod_teamsmeeting\rest\unified::get_o365api($teamsmeeting, $onlinemeeting->teacherid); 
        
        if(0 || ($o365api && $o365api->is_working())) {
            if(!$o365api->tokenallowed) {
                $url = new moodle_url('/mod/teamsmeeting/ucp.php', ['t' => $teamsmeeting->id]);
                if($groupid) {
                    $url->param('group', $groupid);
                }
                $link = \html_writer::link($url, get_string('refreshtoken', 'mod_teamsmeeting'));
                \core\notification::add(get_string('notokeno365', 'mod_teamsmeeting', $link), 
                                        \core\output\notification::NOTIFY_ERROR);    
            }        
        
            //$tokenallowed = true;
        
            if($o365api->tokenallowed) {
                $onlinemeeting->tokenallowed = true;
                if(isset($onlinemeeting->id) && $onlinemeeting->id && $onlinemeeting->meetingid) {
                    // There is a meeting proceed with Validation checkings
                    if(!$o365meeting = $o365api->get_onlinemeeting($onlinemeeting->meetingid)) {
                        $o365meeting = $o365api->get_onlinemeeting_from_url($onlinemeeting->joinurl);
                    }
                    if($o365meeting) {
                        //print_object($o365meeting);
                        //print_object("o365 onlineMeeting GRAPH definition");

                    // check date end
                        $time = new \DateTime($o365meeting['endDateTime']);
                        $time->add(new DateInterval('P60D'));
                        $closingtime = $time->getTimestamp();
                        if($now > $time->getTimestamp()) {
                            // the meeting has expired in office365, discard
                            $DB->set_field('teamsmeeting_onlinemeeting', 'expired', 1, $params + array('id'=>$onlinemeeting->id));
                            //$o365api->delete_onlinemeeting($onlinemeeting->meetingid);
                            //$onlinemeeting->meetingid = '';
                        }
                    } else {
                        //print_object("No response from GRAPH get meeting");
                    }
                } else {
                   //print_object($onlinemeeting); 
                   //print_object("NO GET meeting");
                }
            }
        } else {
            \core\notification::add(get_string('notworkingo365', 'mod_teamsmeeting'), 
                                    \core\output\notification::NOTIFY_ERROR);
            $onlinemeeting->status = OLMEETING_STATE_OFF;
        }
        
    }   
  
    //print_object("saliendo de teamsmeeting_get_valid_onlinemeeting");
  
    $onlinemeeting->access = '';
    $onlinemeeting->openingtime = $teamsmeeting->openingtime;
    $onlinemeeting->closingtime = $teamsmeeting->closingtime;
    
    //print_object($onlinemeeting);
    //print_object("moodle onlinemeeting record");
    
    return $onlinemeeting;
}

/**
 * Gets or creates an onlinemeeting object form DB
 * When accesed by a teacher, resets the waiting  mark time
 *
 * @param string $meetingid o365 ID of the meeting, or weburl
 * @param bool $create if the meeting will be created if not existing
 *
 * @return stdClass
 */
function teamsmeeting_get_onlinemeeting_settings($teamsmeeting, $meetingid) {
    global $DB, $USER;
    
    $meeting = '';
    $isweburl = false;
    //id: "112f7296-5fa4-42ca-bae8-6a692b15d4b8_19:cbee7c1c860e465f8258e3cebf7bee0d@thread.skype"
    //https://teams.microsoft.com/l/meetup-join/19%3a:meeting_NTg0NmQ3NTctZDVkZC00YzRhLThmNmEtOGQ3M2E0ODdmZDZk@thread.v2/0?context=%7b%22Tid%22%3a%aa67bd4c-8475-432d-bd41-39f255720e0a%22%2c%22Oid%22%3a%22112f7296-5fa4-42ca-bae8-6a692b15d4b8%22%7d",
    if(strpos($meetingid, '/meetup-join/') !== false) {
        $isweburl = true;
    }

    $o365api = \mod_teamsmeeting\rest\unified::get_o365api($teamsmeeting); 
    
    if($isweburl) {
        $meeting = $o365api->get_onlinemeeting_from_url($meetingid);
    } else {
        $meeting = $o365api->get_onlinemeeting($meetingid);
    }
    
    return $meeting;
}
