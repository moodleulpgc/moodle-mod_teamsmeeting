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
 * The recording add/edit form.
 *
 * @package     mod_teamsmeeting
 * @copyright   2021 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_teamsmeeting\form; 
 
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');


/**
 * Module instance settings form.
 *
 * @package    mod_teamsmeeting
 * @copyright  2021 Enrique Castro <@ULPGC>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recording_form extends \moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $PAGE;

        $mform = $this->_form;
        
        $item = $this->_customdata['item'];
        $action = ($item) ? 'edit' : 'add';
        
        $mform->addElement('header', 'general', get_string($action.'recording', 'teamsmeeting'));
        
        // Adding the standard "name" field.
        $name = get_string('recordingname', 'teamsmeeting');
        $mform->addElement('text', 'name', $name, ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addHelpButton('name', 'recordingname', 'teamsmeeting');
        $mform->addRule('name', null, 'required', null, 'client');
        
        $name = get_string('recordingurl', 'teamsmeeting');
        $mform->addElement('url', 'streamurl', $name, ['size' => '64']);
        $mform->setType('streamurl', PARAM_URL);
        $mform->addHelpButton('streamurl', 'recordingurl', 'teamsmeeting');
        $mform->addRule('streamurl', null, 'required', null, 'client');
        

        $name = get_string('visible');
        $mform->addElement('advcheckbox', 'visible', $name, ' def    dqsd'); 
        $mform->setDefault('visible', 1);

        $mform->addElement('hidden', 'item', $item);
        $mform->setType('item', PARAM_INT);

        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_ALPHANUMEXT);
        
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'oid', 0);
        $mform->setType('oid', PARAM_INT);
        
        // Submit buttons.
        // Add standard buttons.
        $this->add_action_buttons(true, get_string($action.'recording', 'teamsmeeting'));        
    }
    
    /**
     * Form validation
     *
     * @param array $data data from the form.
     * @param array $files files uploaded.
     * @return array of errors.
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        if (!strpos($data['streamurl'], 'microsoftstream.com/video/')) {
            $errors['streamurl'] = get_string('recoding_nonmsstream_error', 'teamsmeeting');
        }
        return $errors;
    }    
    
    
}
