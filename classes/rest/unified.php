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
 * @package     mod_teamsmeeting
 * @copyright   2020 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_teamsmeeting\rest;

/**
 * Client for unified Office 365 API.
 */
class unified extends \local_o365\rest\unified {

    /** @var bool if the user token is valid and not expired */
    public $tokenallowed = false;
    /** @var string the o365 API to use  */
    protected $endpoint = '';
    /** @var string the o365  System user */
    protected $organizer = '';

    /**
     * Constructor.
     *
     * @param string $caller The calling function, used for logging.
     */
    public function __construct($userid,  $caller = 'construct') {
        try {
            $clientdata = \local_o365\oauth2\clientdata::instance_from_oidc();
        } catch (\Exception $e) {
            \core\notification::add($e->getMessage(), \core\output\notification::NOTIFY_ERROR);
            return false;
        }

        $httpclient = new \local_o365\httpclient();
        $tokenresource = \local_o365\rest\unified::get_tokenresource();

        if (!empty($userid)) {
            $token = \local_o365\oauth2\token::instance($userid, $tokenresource, $clientdata, $httpclient);
            $msg = "o365 api with token for user $userid ";
            //print_object($msg);
            \local_o365\utils::debug($msg, $caller);
        } else {
            $token = \local_o365\utils::get_app_or_system_token($tokenresource, $clientdata, $httpclient);
        }
        
        if (!empty($token)) {
            parent::__construct($token, $httpclient);
        } else {
            $msg = 'Couldn\'t construct Microsoft Graph API client because we didn\'t have a system API user token.';
            $caller = '\mod_teamsmeeting\unified::'.$caller;
            \local_o365\utils::debug($msg, $caller);
        }
        
    }

    /**
     * Factory getter
     * @param object $teamsmeeting the instance record, with times modified for overrides     
     * @return \mod_teamsmeeting\rest\unified class instance
     */
    public static function get_o365api($teamsmeeting, $organizer = 0) {
        global $DB, $USER;
    
        $config = get_config('teamsmeeting');
        
        $userid = null;
        
        $organizerid = '';
        if($config->accessmethod == 0) {
            // access by a user with user token
            $userid = $USER->id;
            $endpoint = '/me/onlineMeetings';
        } elseif($config->accessmethod == 1) {
            // access with app token on behalf of a user
            if(!$organizer) {
                $organizer = $USER->id;
            }
            //$o365user = $DB->get_record('local_o365_objects', ['type' => 'user', 'moodleid' => $USER->id]);
            if(!$organizerid = $DB->get_field('local_o365_objects', 'objectid', 
                                            ['moodleid' => $organizer, 'type'=>'user'])) {
                // this user is not matched, we do not have azureid, return with error
                
                \core\notification::add('user is not matched o365, no ID  ', \core\output\notification::NOTIFY_ERROR);
                return false;                                    
            }
            $endpoint = "/users/{$organizerid}/onlineMeetings";                    
        } elseif($config->accessmethod == 2) {
            // access by organizerid with app token
            $organizerid = $config->organizerid;
            $endpoint = "/users/{$organizerid}/onlineMeetings";                    
        }
        
        
        $o365api = new \mod_teamsmeeting\rest\unified($userid);    
    
        if(0 || $o365api->is_working()) {
            //$tokenallowed = empty($userid) ? true : $o365api->checktoken_valid($userid);    
            $o365api->tokenallowed = empty($userid) ? true : $o365api->checktoken_valid($userid);    
            $o365api->endpoint = $endpoint;
            $o365api->organizer = $organizerid;
        }
   
        $o365api->set_course_cm_context($teamsmeeting);
        
        return $o365api;
    }
    
    /**
     * Ensures course, cm & context objects are loaded in the api instance.
     *
     * @param object $teamsmeeting the instance record, with times modified for overrides
     * @author Enrique Castro <@ULPGC>
     */
    public function set_course_cm_context($teamsmeeting) {    
        if(empty($this->course) || empty($this->cm) || empty($this->context)) {
            list ($course, $cm) = get_course_and_cm_from_instance($teamsmeeting->id,  'teamsmeeting', $teamsmeeting->course);
            $this->course = $course;     
            $this->cm = $cm;     
            $this->context = \context_module::instance($cm->id);     
        }
    }    
    
    /**
     * Switch to disable Microsoft Graph API until release.
     *
     * @return bool Whether the Microsoft Graph API is enabled.
     */
    public function is_working() {
        if (empty($this->httpclient)) {
            return false;
        }
        return true;
    }

    /**
     * Determine whether the supplied token is valid, do NOT refresh.
     *
     * @return bool Whether expiried or not.
     */
    public function checktoken_valid($userid) {

        if(!$userid) {
            return true;
        }
    
        if(empty($this->token) || ($this->token->is_expired() === true)) {
            return false;
        }
        return true;
    }
   
    
    /**
     * Refresh usertoken if necessary.
     */
    public function refreshtoken_here() {
        global $PAGE;
        
        $url = $PAGE->url;
        $context = $PAGE->context;
        if ($this->token->is_expired() === true) {
            $ucptitle = get_string('ucp_title', 'local_o365');
            $ucp = new \local_o365\page\ucp($url, $ucptitle, $context);
            $ucp->run('connecttoken');            
            return false;
        } else {
            return true;
        }
    }    

    /**
     * Prints a o365 tome formatted string  for a unix timestamp
     *
     * @param int $timestamp an unix timestamp time
     * @return string the o365 date time string
     * @author Enrique Castro <@ULPGC>
     */
    public static function format_time($timestamp) {
        $time = new \DateTime("@$timestamp");
        return $time->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * Prints a o365 tome formatted string  for a unix timestamp
     *
     * @param int $timestamp an unix timestamp time
     * @return array the o365 meetingParticipantInfo resource type
     * https://docs.microsoft.com/es-es/graph/api/resources/meetingparticipantinfo?view=graph-rest-1.0
     * @author Enrique Castro <@ULPGC>
     */
    public static function get_user_identity_info($userid, $role = null) {
        global $DB;

        $o365user = $DB->get_record('local_o365_objects', ['type' => 'user', 'moodleid' => $userid]);

        $user = [];
        if($o365user) {
            $user =  ['identity' => ['user' => ['id' =>  $o365user->objectid]],
                      'upn'      => $o365user->o365name];
            if($role) {
                $user['role'] = $role;
            }
        }

        return $user;
    }


    /**
     * Get an onlinemeeting object from o365 using videoTeleconferenceId
     *
     * @param string $vtcid VTC conference id for the meeting
     * @return array the o365 onlinemeeting object as array
     * @author Enrique Castro <@ULPGC>
     */
    public function get_onlinemeeting_from_vtc($vtcid) {
        if(!$this->is_working()) {
            return false;
        }
        $endpoint = '/communications/onlineMeetings/?$filter=VideoTeleconferenceId%20eq%20'."'$vtcid'";
        $response = $this->apicall('get', $endpoint);
        if ($this->httpclient->info['http_code'] == 200) {
            // If response is 200 OK, return response.
            $expectedparams = ['id' => null];
            return $this->process_apicall_response($response, $expectedparams);
        } else {
            return false;
        }
    }


    /**
     * Get an onlinemeeting object from o365 using videoTeleconferenceId
     *
     * @param string joinurl onlineMeeting joinweburl in o365 for that meeting
     * @return array the o365 onlinemeeting object as array
     * @author Enrique Castro <@ULPGC>
     */
    public function get_onlinemeeting_from_url($joinurl) { 
        if(!$this->is_working()) {
            return false;
        }
        
        $target = '?$filter=JoinWebUrl%20eq%20'."'".$joinurl."'";
        
        $endpoint = $this->endpoint.$target;
        $response = $this->betaapicall('get', $endpoint);
        if ($this->httpclient->info['http_code'] == 200) {
            // If response is 200 OK, return response.
            $expectedparams = ['id' => null];
            return $this->process_apicall_response($response, $expectedparams);
        } else {
            return false;
        }
    }
    
    /**
     * Get an onlinemeeting object from o365 using videoTeleconferenceId
     *
     * @param string $vtcid VTC conference id for the meeting
     * @return array the o365 onlinemeeting object as array
     * @author Enrique Castro <@ULPGC>
     */
    public function get_onlinemeeting($meetingid) {
        if(!$this->is_working()) {
            return false;
        }
        
        $endpoint = $this->endpoint.'/'.$meetingid;
        $response = $this->betaapicall('get', $endpoint);
        if ($this->httpclient->info['http_code'] == 200) {
            // If response is 200 OK, return response.
            $expectedparams = ['id' => null];
            return $this->process_apicall_response($response, $expectedparams);
        } else {
            //print_object($response);
            return false;
        }
    }
    
    /**
     * Creates a new o365 payload structure
     *
     * @param object $teamsmeeting the instance record, with times modified for overrides
     * @return array suitable as input in o365 payload
     * @author Enrique Castro <@ULPGC>
     */
    public function build_common_meetingdata($teamsmeeting) {
        $meetingdata = [];

        if($teamsmeeting->openingtime) {        
            $starttime = $teamsmeeting->openingtime;
        } else {
            $starttime = strftime('%Y-%m-%d %H:%M');
            $min = substr($starttime, -2); 
            $starttime = substr($starttime, 0, -2);
            $minute = (int)$min;
            if($minute < 8) {
                $min = '00';
            } elseif($minute < 23) {
                $min = '15';
            } elseif($minute < 38) {
                $min = '30';
            } elseif($minute < 53) {
                $min = '45';
            }
            $starttime .= $min; 
            $starttime = strtotime($starttime);
        }        
    
        if($teamsmeeting->allowedpresenters == TEAMSMEETING_PRESENTER_CHANNEL) {
            $teamsmeeting->allowedpresenters = TEAMSMEETING_PRESENTER_BYROLE;
        }
        
        $boolean = [0 => 'false', 1 => 'true'];
    
        $meetingdata = [
            'startDateTime' => self::format_time($starttime),
            'subject' => format_string($teamsmeeting->name),
            'allowedPresenters' => 'roleIsPresenter',
            'lobbyBypassSettings' => ['scope' => $teamsmeeting->lobbybypass, 
                                      'isDialInBypassEnabled' => $boolean[(int)$teamsmeeting->lobbybypass]],
            'isEntryExitAnnounced' => $boolean[(int)$teamsmeeting->joinannounce],
        ];

        if($teamsmeeting->allowedpresenters == TEAMSMEETING_PRESENTER_ORG) {
            $meetingdata['allowedPresenters'] = 'organizer';
        }
        
        $endtime = $teamsmeeting->closingtime ? $teamsmeeting->closingtime : $starttime + 3600;
        $meetingdata['endDateTime'] = self::format_time($endtime);    
    
        return $meetingdata;
    }
    
    /**
     * Builds an attendees array of meetingParticipantInfo for presenter users.
     *
     * @param object $teamsmeeting the instance record, with times modified for overrides
     * @param int $groupid and ID for a moodle group this meeting is associated with
     * @return array suitable as input in o365 payload
     * @author Enrique Castro <@ULPGC>
     */
    public function load_presenters($teamsmeeting, $groupid) {    
        global  $USER;
        
        $attendees = [];
        $users = [];
        
        if($teamsmeeting->allowedpresenters == TEAMSMEETING_PRESENTER_ORG) {
            if(!empty($this->organizer) && is_enrolled($this->context, null, 'mod/teamsmeeting:present')) {
                $users = [$USER->id => $USER];
            }
        } elseif($teamsmeeting->allowedpresenters == TEAMSMEETING_PRESENTER_COURSE) {
            $users = get_enrolled_users($this->context, 'mod/teamsmeeting:present', 0, 'u.id, u.username, u.idnumber');        
        } elseif($teamsmeeting->allowedpresenters == TEAMSMEETING_PRESENTER_GROUP) {
            $users = get_enrolled_users($this->context, 'mod/teamsmeeting:present', $groupid, 'u.id, u.username, u.idnumber');
            
            $users = $users + array_intersect_key(get_enrolled_users($this->context, 'mod/teamsmeeting:present', 0, 'u.id, u.username, u.idnumber'),
                                                  get_enrolled_users($this->context, 'moodle/site:accessallgroups', 0, 'u.id, u.username, u.idnumber'));
            
        }
        
        foreach($users as $auser) {
                $attendees[$auser->id] = self::get_user_identity_info($auser->id, 'presenter');
        }
        
        // array_filter removes empty values
        return array_filter($attendees);
    }
    
    /**
     * Construct attendees array from course enrolment
     *
     * @param int $groupid an ID for a moodle group
     * @return array meetingParticipantInfo collection
     * @author Enrique Castro <@ULPGC>
     */
    public function load_enrolled_users($groupid) {
        global $DB; 
        
        $attendees = [];
        list($esql, $params) = get_enrolled_sql($this->context, 'mod/teamsmeeting:join', $groupid, true);
        $sql = "SELECT u.id, u.username, o.objectid, o.o365name
                    FROM {user} u
                    JOIN ($esql) je ON je.id = u.id
                    JOIN {local_o365_objects} o ON o.type = 'user' AND o.moodleid = u.id
                WHERE u.deleted = 0 AND o.objectid != '' 
                ORDER BY u.lastname, u.firstname ";
        $users = $DB->get_records_sql($sql, $params);
        
        $user =  ['identity' => ['user' => ['id' =>  '']],
                    'upn'    => '',
                    'role'   => 'attendee'];
        foreach($users as $o365user) {
            $user['identity']['user']['id'] = $o365user->objectid;
            $user['upn'] = $o365user->o365name;
            $user['role'] = 'attendee';
            $attendees[$o365user->id] = $user;
        }
        
        // array_filter removes empty values
        return array_filter($attendees);
    }
    

    /**
     * Creates a new o365 an onlineMeeting object
     *
     * @param object $teamsmeeting the instance record, with times modified for overrides
     * @param int $groupid an ID for a moodle group
     * @return mixed false/onlinemeeting record structure
     * @author Enrique Castro <@ULPGC>
     */
    public function get_primary_channel($courseteamid) {
        if(!$this->is_working()) {
            return false;
        }
        
        $endpoint = "/teams/$courseteamid/primaryChannel";
        $response = $this->betaapicall('get', $endpoint);
        if ($this->httpclient->info['http_code'] == 200) {
            // If response is 200 OK, return response.
            $expectedparams = ['id' => null];
            $channeldata = $this->process_apicall_response($response, $expectedparams);
            return $channeldata['id'];
        } else {
            return false;
        }    
    }  
    
    /**
     * Creates a new o365 an onlineMeeting object
     *
     * @param object $teamsmeeting the instance record, with times modified for overrides
     * @param int $groupid an ID for a moodle group
     * @return mixed false/onlinemeeting record structure
     * @author Enrique Castro <@ULPGC>
     */
    public function add_channel_chatinfo($courseid, $groupid) {
        global $CFG, $DB, $USER;    
    
        $chatinfo = [];
        $channelid = '';
        if(!$groupid) {
            //all participants means the general channel
            $params = ['type' => 'group', 'subtype' => 'courseteam', 'moodleid' => $courseid];
            if($courseteamsrec = $DB->get_record('local_o365_objects', $params)) {
                $channelid = $this->get_primary_channel($courseteamsrec->objectid);
            }
        } else {
            $params = ['type' => 'group', 'subtype' => 'teamchannel', 'moodleid' => $groupid];
            if($teamschannelrec = $DB->get_record('local_o365_objects', $params)) {
                $channelid = $teamschannelrec->objectid;
            }
        }
    
        if($channelid) {
            $chatinfo['threadId'] = $channelid; 
        }
        
        return $chatinfo;
    }
    
    /**
     * Creates a new o365 an onlineMeeting object
     *
     * @param object $teamsmeeting the instance record, with times modified for overrides
     * @param int $groupid an ID for a moodle group
     * @return mixed false/onlinemeeting record structure
     * @author Enrique Castro <@ULPGC>
     */
    public function create_onlinemeeting($teamsmeeting, $groupid) {
        global $CFG, $DB, $USER;

        if(1 && !$this->is_working()) {
            return false;
        }
        
        $meetingdata = $this->build_common_meetingdata($teamsmeeting);
        $presenters = $this->load_presenters($teamsmeeting, $groupid);

        $attendees = $this->load_enrolled_users($groupid);
        $chatinfo = $this->add_channel_chatinfo($teamsmeeting->course, $groupid);
        
        /*
        if($teamsmeeting->membership == TEAMSMEETING_MEMBER_AUTO) {
            $attendees = $this->load_enrolled_users($groupid);
        } elseif($teamsmeeting->membership == TEAMSMEETING_MEMBER_CHANNEL) {
            $chatinfo = $this->add_channel_chatinfo($teamsmeeting->course, $groupid);
            if(empty($chatinfo) && isset($teamsmeeting->allowedpresenters) && 
                        (($teamsmeeting->allowedpresenters == TEAMSMEETING_PRESENTER_BYROLE) ||
                        ($teamsmeeting->allowedpresenters == TEAMSMEETING_PRESENTER_ORG))) {
                // if NO channel defined, and not ALL allowed, make explicit enrolment 
                $attendees = $this->load_enrolled_users($groupid);
            }
        }
        */
        
        if(!empty($chatinfo)) {
            //$meetingdata['chatInfo'] = $chatinfo;
        }
        
        // add users to meeting
        if(!empty($presenters) || !empty($attendees)) {
            // if not set, organizer is the current user
            if(!empty($this->organizer)) {
                $systemorganizer = get_config('teamsmeeting', 'organizerid');
                if($this->organizer == $systemorganizer) {
                    $organizer = ['identity' => ['user' => ['id' => $this->organizer]],
                                    'upn'      => 'cvulpgc@ulpgc.es',
                                    'role' => 'presenter'];
                } else {
                    $organizer = self::get_user_identity_info($USER->id, 'presenter');
                }
            } else {
                $organizer = self::get_user_identity_info($USER->id, 'presenter');
            }
            
            $meetingdata['participants']['organizer'] = $organizer;
            $meetingdata['participants']['attendees'] = array_filter(array_values($presenters + $attendees));
        }

        /*
        if($teamsmeeting->allowedpresenters == TEAMSMEETING_PRESENTER_ORG) {
            $meetingdata['allowedPresenters'] = 'organizer';
        } else {
            $meetingdata['allowedPresenters'] = 'roleIsPresenter';
        }
        */
        //$meetingdata['externalId'] = $CFG->siteidentifier.'_'.$this->course->shortname.'_'.$teamsmeeting->id.'-'. $groupid.'-'.random_string(30).'-'.time();
        //$meetingdata['allowedPresenters'] = 'roleIsPresenter';
        
        //print_object(str_replace(',', "\n", json_encode($meetingdata)));
        //print_object($meetingdata);
        //print_object("o365 meetingdata definition");
        if(!$this->is_working()) {
            //print_object(" o365 is NOt working");
            return false;
        }        
        
        // createorget to ensure externalID is accepted
        //$endpoint = $this->endpoint.'/createOrGet';
        $endpoint = $this->endpoint;

        $response = $this->betaapicall('post', $endpoint, json_encode($meetingdata));
        if ($this->httpclient->info['http_code'] == 201) {
            // If response is 201 created, return response.
            $expectedparams = ['id' => null];
            if($o365meeting =  $this->process_apicall_response($response, $expectedparams)) {
                $now = time();
                $onlinemeeting = new \stdClass();
                $onlinemeeting->teamsmeetingid = $teamsmeeting->id;
                $onlinemeeting->groupid = $groupid;
                $onlinemeeting->teacherid = $USER->id;
                $onlinemeeting->meetingid = $o365meeting['id'];
                $onlinemeeting->externalid = $o365meeting['externalId'];
                $onlinemeeting->vtcid = $o365meeting['videoTeleconferenceId'];
                $onlinemeeting->joinurl = $o365meeting['joinWebUrl'];
                $onlinemeeting->chatinfotid = $o365meeting['chatInfo']['threadId'];
                $onlinemeeting->status = OLMEETING_STATE_OK;
                $onlinemeeting->endtime = 0;
                $onlinemeeting->expired = 0;
                $onlinemeeting->timeactivated = 0;
                $onlinemeeting->timecreated = $now;
                $onlinemeeting->timemodified = $now;

                if($onlinemeeting->id = $DB->insert_record('teamsmeeting_onlinemeeting', $onlinemeeting)) {
                    //print_object($o365meeting);
                    return $onlinemeeting;
                }
            }
        } else {
            //Error.
            $response = json_decode($response);
            \core\notification::error(get_string('error_o365', 'mod_teamsmeeting', $response->error->message));
        }

        return false;
    }

    
    /**
     * Get an onlinemeeting object from o365 using videoTeleconferenceId
     *
     * @param string $vtcid VTC conference id for the meeting
     * @return array the o365 onlinemeeting object as array
     * @author Enrique Castro <@ULPGC>
     */
    public function update_onlinemeeting($meetingid, $meetingdata) { // endpoint is only for testing
        if(!$this->is_working()) {
            return false;
        }
        
        $endpoint = $this->endpoint.'/'.$meetingid;
        $response = $this->betaapicall('patch', $endpoint, json_encode($meetingdata));
        if ($this->httpclient->info['http_code'] == 200) {
            // If response is 200 OK, return response.
            $expectedparams = ['id' => null];
            return $this->process_apicall_response($response, $expectedparams);
        } else {
            return false;
        }
    }    
    
    /**
     * Removes an onlinemeeting object from o365
     *
     * @param string $meetingid office365  id for the meeting
     * @return array the o365 onlinemeeting object as array
     * @author Enrique Castro <@ULPGC>
     */
    public function delete_onlinemeeting($meetingid) {
        $endpoint = $this->endpoint.'/'.$meetingid;
        $response = $this->apicall('delete', $endpoint);
        if ($this->httpclient->info['http_code'] == 204) {
            // If response is 204 No content, return success.
            return true;
        } else {
            return false;
        }
    }
    
}
