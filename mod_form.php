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
 * The main mod_teamsmeeting configuration form.
 *
 * @package     mod_teamsmeeting
 * @copyright   2020 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_teamsmeeting
 * @copyright  2020 Enrique Castro <@ULPGC>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_teamsmeeting_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('teamsmeetingname', 'mod_teamsmeeting'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'teamsmeetingname', 'mod_teamsmeeting');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the rest of mod_teamsmeeting settings, spreading all them into this fieldset
        $mform->addElement('header', 'teamsmeetingheader', get_string('teamsmeetingheader', 'mod_teamsmeeting'));
        $mform->setExpanded('teamsmeetingheader');

        /*
        $name = get_string('openingtime', 'mod_teamsmeeting');
        $mform->addElement('date_time_selector', 'openingtime', $name, array('optional'=>true));
        $mform->addHelpButton('openingtime', 'openingtime', 'mod_teamsmeeting');

        $name = get_string('closingtime', 'mod_teamsmeeting');
        $mform->addElement('date_time_selector', 'closingtime', $name, array('optional' => true));
        $mform->addHelpButton('closingtime', 'closingtime', 'mod_teamsmeeting');
        */
        
        $options = [TEAMSMEETING_MEMBER_AUTO => get_string('memberauto', 'mod_teamsmeeting'), 
                    //TEAMSMEETING_MEMBER_MANUAL => get_string('membermanual', 'mod_teamsmeeting'), 
                    //TEAMSMEETING_MEMBER_CHANNEL => get_string('memberchannel', 'mod_teamsmeeting'), 
                    ];
        $name = get_string('membership', 'mod_teamsmeeting');        
        $mform->addElement('select', 'membership', $name, $options);
        $mform->addHelpButton('membership', 'membership', 'mod_teamsmeeting');
        $mform->setDefault('membership', TEAMSMEETING_MEMBER_CHANNEL);        
        
        $options = [//TEAMSMEETING_PRESENTER_ANY => get_string('presenterany', 'mod_teamsmeeting'), 
                    //TEAMSMEETING_PRESENTER_ALL => get_string('presenterall', 'mod_teamsmeeting'),
                    //TEAMSMEETING_PRESENTER_CHANNEL => get_string('presenterbychannel', 'mod_teamsmeeting'), 
                    //TEAMSMEETING_PRESENTER_BYROLE => get_string('presenterbyrole', 'mod_teamsmeeting'), 
                    TEAMSMEETING_PRESENTER_COURSE => get_string('presenterbycourse', 'mod_teamsmeeting'),
                    TEAMSMEETING_PRESENTER_GROUP => get_string('presenterbygroup', 'mod_teamsmeeting'), 
                    TEAMSMEETING_PRESENTER_ORG => get_string('organizeronly', 'mod_teamsmeeting'), 
                    ];
        $name = get_string('allowedpresenters', 'mod_teamsmeeting');        
        $mform->addElement('select', 'allowedpresenters', $name, $options);
        $mform->addHelpButton('allowedpresenters', 'allowedpresenters', 'mod_teamsmeeting');
        $mform->setDefault('allowedpresenters', TEAMSMEETING_PRESENTER_CHANNEL);        
        
        $options = [TEAMSMEETING_LOBBY_NONE => get_string('organizeronly', 'mod_teamsmeeting'), 
                    TEAMSMEETING_LOBBY_ORG => get_string('lobbynonorg', 'mod_teamsmeeting'),
                    TEAMSMEETING_LOBBY_ORGFED => get_string('lobbynonfed', 'mod_teamsmeeting'), 
                    TEAMSMEETING_LOBBY_ALL => get_string('lobbynone', 'mod_teamsmeeting'), 
                    ];
        $name = get_string('lobbybypass', 'mod_teamsmeeting');        
        $mform->addElement('select', 'lobbybypass', $name, $options);
        $mform->addHelpButton('lobbybypass', 'lobbybypass', 'mod_teamsmeeting');
        $mform->setDefault('lobbybypass', TEAMSMEETING_LOBBY_ORG);
        
        //Whether or not to announce when users join or leave.
        $name = get_string('joinannounce', 'mod_teamsmeeting');        
        $mform->addElement('selectyesno', 'joinannounce', $name);
        $mform->addHelpButton('joinannounce', 'joinannounce', 'mod_teamsmeeting');
        $mform->setDefault('joinannounce', 0);
        
        
        $name = get_string('waitmoderator', 'mod_teamsmeeting');
        $mform->addElement('duration', 'wait', $name);
        
        //$mform->addElement('duration', 'timelimit', get_string('timelimit', 'quiz'),
        //$mform->addElement('advcheckbox', 'wait', $name);
        $mform->addHelpButton('wait', 'waitmoderator', 'mod_teamsmeeting');
        
        
        
        /*
        $name = get_string('useafterwards', 'mod_teamsmeeting');
        $mform->addElement('advcheckbox', 'useafterwards', $name);
        $mform->addHelpButton('useafterwards', 'useafterwards', 'mod_teamsmeeting');

        if($showrecordings = get_config('teamsmeeting', 'showrecordings')) {
            $name = get_string('showrecordings', 'mod_teamsmeeting');
            $mform->addElement('advcheckbox', 'showrecordings', $name);
            $mform->addHelpButton('showrecordings', 'showrecordings', 'mod_teamsmeeting');
        } else {
            $mform->addElement('hidden', 'showrecordings', 0);
            $mform->setType('showrecordings', PARAM_INT);
        }
        
        $mform->addElement('text', 'externalurl', get_string('url', 'msteams'), array('size'=>'48'));
        $mform->addHelpButton('externalurl', 'url', 'msteams');
        $mform->setType('externalurl', PARAM_RAW_TRIMMED);
        
        */
        
        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
    
    /**
     * Perform minimal validation on the settings form
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['openingtime']) && empty($data['closingtime'])) {
                $errors['closingtime'] = get_string('closingtimeempty', 'mod_teamsmeeting');
        }
        
        if (!empty($data['openingtime']) && !empty($data['closingtime'])) {
            if ($data['closingtime'] < $data['openingtime'] ) {
                $errors['closingtime'] = get_string('closingtimeearly', 'mod_teamsmeeting');
            }
        }
        
        if (empty($data['openingtime']) && !empty($data['closingtime'])) {
                $errors['openingtime'] = get_string('openingtimeempty', 'mod_teamsmeeting');
        }
        

        return $errors;
    }

    
}
