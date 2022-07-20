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
 * Plugin strings are defined here.
 *
 * @package     mod_teamsmeeting
 * @category    string
 * @copyright   2020 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addrecording'] = 'Add recording';

$string['backtocourse'] = 'Back to course';
$string['backtomodule'] = 'Back to recordings';
$string['confirmrecordingdelete'] = 'You have asked to remove the recording "{$a}". <br /> 
 Do you want to proceed? ';
$string['editrecording'] = 'Edit recording';
$string['recording'] = 'Recording';
$string['recording_help'] = 'The Add/Edit recording page allows to add a video recording existing as a MS-Stream url. 

You must add a name to idenitfy the recoding. 
Participants will be able to play the video at any moment while the recordig is set vsiible. 

Please, make sure that the MS-Stream video has appropiate permissions be available for the intendedn perticipants. 
Moodle cannot manage MS-Stream permissions. ';
$string['recordingmodified'] = '(modified on {$a})';
$string['recordingsaved'] = 'Recording has been successfully added or updated.';
$string['recordingnotsaved'] = 'Not saved. Adding or updating recording "{&a}" has failed. ';
$string['recoding_nonmsstream_error'] = 'Invalid MS-Stream url. Please, add microsoftstream.com urls';
$string['recordings'] = 'Recordings';
$string['recordingname'] = 'Name';
$string['recordingname_help'] = 'An identifiying name for this particular recording.';
$string['recordingurl'] = 'The MS-Stream url of the video recording.';
$string['recordingurl_help'] = 'The MS-Stream url of the video recording. 
Please, ensure that the Stream entry has appropiate permissions to be accesible by the relevant participants. 

Just copy-paste or select from MS-Stream. ';

$string['allowedpresenters'] = 'Presenters';
$string['allowedpresenters_help'] = 'Who can act as Presenter initially. 
    
 * Everyone: Anyone in the meeting is a presenter, from any procedence.
 * Institution: Everyone in organizer’s Institution is a presenter.
 * Course: Course teachers are presenters. 
 * Organizer: Only the user assigned as meeting organizer is a Presenter. 
';
$string['accessasuser'] = 'Users as Organizers';
$string['accessasuser_desc'] = 'If enabled, then meetings will be created with a moodle user as Organizer. 
The user must have access to o365, and a user token must be provided (Explicit o365 login or credential renewal).';
$string['deleteremote'] = 'Remove remote';
$string['deleteremote_desc'] = 'When checked, if the moodle instance is deleted then 
the system will try to remove remote Azure resources associated.';
$string['updateremote'] = 'Update remote';
$string['updateremote_desc'] = 'When checked, if the moodle instance is updated then 
the system will try to update settings in remote Azure resources associated.';
$string['o365accessmode'] = 'o365 Access mode';
$string['o365accessmode_desc'] = 'Teamsmeeting create "institutional" o365 onlineMeetings preferentially. 
This is, the meeting organizer is an institutional user for all meetings. 
Once created, an o365 onlineMeetings can only be updated by the Organizer user. 
This is is a problem in sites where courses have frequently several teachers.

Institutional access needs a definite user in o365 with Privileges and Policies applied to manage onlineMeetings across organization tenant.
The Organizer user can update any onlineMeetings created by Teamsmeeting from moodle module instance updated by any course teacher. 
The meeting is thus a course meeting rather than a personal one.  
Teachers are added as Presenters to the meeting and has full presentation capacities during video meeting.  

If courses have only one teacher, and the same user will manage course meetings, 
or if you prefer explicit user authorization and personal meeting ownership, 
the access mode may be set to a user-based one. Enabling that mode hides the Organizer user settings.
';
$string['operationsettings'] = 'Operational settings';
$string['organizeruser'] = 'Organizer user';
$string['organizeruser_desc'] = 'If filled, all teamsmeetings are created in o365 with this System user as "Organizer". 
This parameter is an azure ID for a user with Privileges and Policies manage onlineMeetings.';
$string['presenterany'] = 'Everyone';
$string['presenterall'] = 'Institution members';
$string['presenterbychannel'] = 'Channel teachers';
$string['presenterbycourse'] = 'Course teachers';
$string['presenterbygroup'] = 'Group teachers';
$string['presenterbyrole'] = 'Course/group teachers';
$string['organizeronly'] = 'Organizer only';
$string['lobbybypass'] = 'Access lobby bypass';
$string['lobbybypass_help'] = 'Some users can bypass the meeting lobby and access directly to the meeting.
';
$string['lobbynonorg'] = 'Institution members';
$string['lobbynonfed'] = 'Institution and Partner members';
$string['lobbynone'] = 'All users';
$string['joinannounce'] = 'Announce Join/Exit';
$string['joinannounce_help'] = 'Whether or not to announce when users join or leave the meeting.';

$string['pluginname'] = 'MS-Teams videoconference meeting';
$string['pluginadministration'] = 'MS-Teams videoconference administration';
$string['teamsmeeting:addinstance'] = 'Add new Teams Meeting';
$string['teamsmeeting:create'] = 'Create new meetings in Office365';
$string['teamsmeeting:join'] = 'Join to the meeting';
$string['teamsmeeting:present'] = 'Being meeting Presenter';
$string['teamsmeeting:managerecordings'] = 'Manage recordings';

$string['closingtimeempty'] = 'Closing time must be set if an opening time is specified.';
$string['closingtimeearly'] = 'Closing time must be later than opening time.';
$string['eventmeetingjoined'] = 'MS-Teams meeting joined';
$string['expirationdays'] = 'Meeting expiration';
$string['expirationdays_desc'] = 'Office365 meetings are available for re-use up these these days after closing date';
$string['forgroup'] = 'For {$a}: ';
$string['groupsnoone'] = 'The meeting has been configured for using groups but you have no group membership in this context.';
$string['groupsmultiple'] = 'There are separate meetings for different groups, and you have access to several ones. 
Be sure to select the appropiate one for the intended use.';
$string['groupsnotvisible'] = 'Students cannot access  "All participants" in Separate Groups mode';
$string['overrideslink'] = 'Dates by group';
$string['overridesexplain'] = 'Meeting dates may be set for each group at {$a}.';
$string['override'] = 'Override';
$string['overrideadded'] = 'Override added for group {$a}';
$string['overridedeleted'] = 'Override deleted for group {$a}';
$string['overrideerror'] = 'Some error ocurred when trying to add, update or delete, override group {$a}. Nothing done.';
$string['overrideexisting'] = 'Override already existing for group {$a}. Not added new one.';
$string['overrideaddnew'] = 'Add new group override';
$string['overrideupdated'] = 'Override updated for group {$a}.';
$string['overridedelconfirm'] = 'You have asked to remove the override for group {$a}. <br />
This action will try to remove the ISL onlinemeeting code as well. Do you wnat to proceed?';
$string['overrideinactivehelp'] = 'Override inactive';
$string['overridenot'] = 'There are no overrides';
$string['overridesaveandstay'] = 'Save and add new one';
$string['reverttodefault'] = 'Revert to default';

$string['teacher'] = 'Teacher';
$string['joinurllink'] = 'Link to Teams meeting';
$string['joinbutton'] = 'Join to onlinemeeting';
$string['createbutton'] = 'Create onlinemeeting';
$string['updatebutton'] = 'Update onlinemeeting';

$string['notifytime'] = 'Reminder beforetime';
$string['notifytime_help'] = 'If not zero, the time before the meeting opening to send reminders to participants';

$string['meetingclosed'] = 'Meeting finished on {$a}.';
$string['meetingcloseson'] = 'Meeting finishes on {$a}.';
$string['meetingopenedon'] = 'Meeting started on {$a}.';
$string['meetingnotavailable'] = 'Meeting not available until {$a}.';
$string['memberauto'] = 'Automatic';
$string['membermanual'] = 'Manual';
$string['membership'] = 'Meeting membership';
$string['membership_help'] = 'How to specify or select the users that can join/attend the meeting. 

 * Auto: Controlled by course and group enrolment.
 * Manual: The teacher can specify which other users can coin or attend the meeting. 
';
$string['modulename'] = 'MS-Teams videoconference';
$string['modulenameplural'] = 'MS-Teams videoconference';

$string['openingtime'] = 'Join opens';
$string['openingtime_help'] = 'If used, the date and time set for strating the meeing. 

It may be left empty, meaning a permanent onlinemeeting that is started at any time by a teacher by joining the meeting. ';
$string['openingtimeempty'] = 'Opening time must be set if a closing time is specified.';
$string['closingtime'] = 'Join closed';
$string['closingtime_help'] = 'If used, the finishing date and time for the meeting. 

Closing time can only be used if an Opening time is set, and must be later than the opening time. 
';
$string['nothingtosee'] = 'You cannot access these meetings';
$string['invalid365organizer'] = 'You must be enrolled and connected to o365 to create a meeting as Organizer.';

$string['recordingstitle'] = 'Recordings (in o365)';


$string['showrecordings'] = 'Show recordings';
$string['showrecordings_help'] = 'If enabled, a table will be displayed with recordings stored at MS-Stream for these meetings.';
$string['showrecordings_desc'] = 'If enabled, users may select to show a table with recordings performed in these meetings. <br />
This parameter does NOT control if o365 can allow recordings or not.';
$string['teacheradvance'] = 'Teacher in advance joining';
$string['teacheradvance_desc'] = 'Teachers may join to meeting this time before the specified opening time.';

$string['teamsmeetingheader'] = 'Meeting options';
$string['teamsmeetingname'] = 'Meeting name';
$string['teamsmeetingname_help'] = "This is the name displayed in the course page and in Office365.  

Please, dot not use characters like, '\' that are removed by Office365, if you want to preserve consistency in both systems.";
$string['waitformoderator'] = 'Please, wait for a teacher to join';
$string['waitforcreation'] = 'Meeting is not ready in Office365. Please, wait for meeting to be created';
$string['waitmoderator'] = 'Wait for teacher';
$string['waitmoderator_help'] = 'If enabled, the student can join only after a teacher (any) has joined and started the meeting.';
$string['waitpastdue'] = 'You are past due after teacher activation. Please, try next session';
$string['useafterwards'] = 'Keep available after closing';
$string['useafterwards_help'] = 'If enabled, the meeting will remain available for joining again while Office365 keeps the meeting live (usually 2 months).';
$string['error_o365'] = 'o365 access error: {$a}.';
$string['notworkingo365'] = 'Connection to Office365 is NOT configured; It is NOT possible to manage MS-Teams meetings.';
$string['notokeno365'] = 'Connection to Office365 has expired. You need to <strong>{$a}</strong> to be able to manage o365 videoconference settings.';
$string['refreshtoken'] = 'refresh o365 connection';
$string['viewinstream'] = 'View recording in MS-Stream';
///////// temporal
///////// temporal
///////// temporal
$string['authtype'] = 'Auth type';
$string['authtype_help'] = 'Auth type on o365, me/  or application permissions.';
$string['authuser'] = 'User';
$string['authapp'] = 'App';
$string['authappcv'] = 'App (cvulpgc)';
$string['memberchannel'] = 'By channel';
