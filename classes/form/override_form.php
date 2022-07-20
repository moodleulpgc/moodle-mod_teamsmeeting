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
 * The override add/edit form.
 *
 * @package     mod_teamsmeeting
 * @copyright   2020 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_teamsmeeting\form;
 
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');


/**
 * Module instance settings form.
 *
 * @package    mod_teamsmeeting
 * @copyright  2020 Enrique Castro <@ULPGC>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class override_form extends \moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $PAGE;

        $mform = $this->_form;
        
        $groups = $this->_customdata['groups'];
        $item = $this->_customdata['item'];
        $default = $this->_customdata['default'];
        
        $mform->addElement('header', 'general', get_string('override', 'teamsmeeting'));
        
        $groupchoices = array();
        if($item) {
            $groupchoices[$default->groupid] = $groups[$default->groupid]->name;
        } else {
            foreach ($groups as $group) {
                $groupchoices[$group->id] = $group->name;
            }
            $default->groupid = 0;
        }
        if (count($groupchoices) == 0) {
            $groupchoices[0] = get_string('none');
        }        
        $mform->addElement('select', 'groupid',
                get_string('group'), $groupchoices);
        $mform->addRule('groupid', get_string('required'), 'required', null, 'client');
        $mform->setDefault('groupid', $default->groupid);        
        
        $name = get_string('openingtime', 'mod_teamsmeeting');
        $mform->addElement('date_time_selector', 'openingtime', $name, array('optional'=>true));
        $mform->addHelpButton('openingtime', 'openingtime', 'mod_teamsmeeting');
        $mform->setDefault('openingtime', $default->openingtime);        

        $name = get_string('closingtime', 'mod_teamsmeeting');
        $mform->addElement('date_time_selector', 'closingtime', $name, array('optional' => true));
        $mform->addHelpButton('closingtime', 'closingtime', 'mod_teamsmeeting');
        $mform->setDefault('notifytime', $default->closingtime);        

        $name = get_string('notifytime', 'mod_teamsmeeting');        
        $mform->addElement('duration', 'notifytime', $name);
        $mform->addHelpButton('notifytime', 'notifytime', 'mod_teamsmeeting');
        $mform->setDefault('notifytime', $default->notifytime);        
        
        $action = 'add';
        if($item) {
            $action = 'edit';
            $mform->addElement('hidden', 'item', $item);
            $mform->setType('item', PARAM_INT);
        }
        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_ALPHANUMEXT);
        
        $mform->addElement('hidden', 'id', $PAGE->cm->id);
        $mform->setType('id', PARAM_INT);
        
        // Submit buttons.
        $mform->addElement('submit', 'resetbutton',
                get_string('reverttodefault', 'teamsmeeting'));

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton',
                get_string('save'));
        $buttonarray[] = $mform->createElement('submit', 'againbutton',
                get_string('overridesaveandstay', 'teamsmeeting'));
        $buttonarray[] = $mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonbar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonbar');
    }
    
}
