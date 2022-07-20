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
 * Renderer.
 *
 * @package   mod_teamsmeetings
 * @copyright 2020 Enrique Castro <@ULPGC>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Darko Miletic  (darko.miletic [at] gmail [dt] com)
 */

namespace mod_teamsmeeting\output;

use plugin_renderer_base;
use html_writer;
use html_table;
use pix_icon;
use moodle_url;
use single_select;
use single_button;

defined('MOODLE_INTERNAL') || die();

/**
 * Class renderer
 * @package   mod_teamsmeetings
 * @copyright 2020 Enrique Castro <@ULPGC>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Darko Miletic  (darko.miletic [at] gmail [dt] com)
 */
class renderer extends plugin_renderer_base {

    /**
     * Renderer for index.
     * @param  index $indexobj
     * @return string
     */
    protected function render_index(index $indexobj) {
        return html_writer::table($indexobj->table);
    }


    /**
     * Prints the join  button .
     * @param  stdClass $onlinemeeting  an object with joinurl 
     * @param  string $forgroupname the group name      
     * @return string
     */
    public function show_join_button($onlinemeeting, $forgroupname = '', $cancreate = false) {
        global $CFG;
    
        $now = time();
        $url = new moodle_url($CFG->wwwroot.'/mod/teamsmeeting/join.php', array('id'=>$this->page->cm->id));
        $url->param('joinurl', $onlinemeeting->joinurl);
        $url->param('oid', $onlinemeeting->id);
        if($cancreate) {
            
            $url->param('activate', time());
        }
        
        $button =  $this->single_button($url, get_string('joinbutton', 'teamsmeeting'), 'post', array('target'=>'_blank'));  
        
        return  $this->container($forgroupname . $button, ' actionbutton ');  
    }
    
    /**
     * Prints the create or update button .
     * 
     * @param  stdClass $onlinemeeting  an object with joinurl 
     * @param  string $forgroupname the group name 
     * @return string
     */
    public function show_create_update_button($onlinemeeting, $forgroupname = '') {
        global $CFG;
    
        if($onlinemeeting->status == OLMEETING_STATE_OFF) {
            return $this->box(get_string('notworkingo365', 'teamsmeeting'), 'box centerpara  alert-warning text-danger');
        }
    
        $target = 'ucp.php';
        if(isset($onlinemeeting->tokenallowed) &&  $onlinemeeting->tokenallowed) {
            $target = 'view.php';
        }
        $url = new moodle_url($CFG->wwwroot.'/mod/teamsmeeting/'.$target, ['t'=>$onlinemeeting->teamsmeetingid]);
        if($onlinemeeting->groupid) {
            $url->param('group', $onlinemeeting->groupid);
        }
        
        //print_object("target = $target");
        
        if(isset($onlinemeeting->id) && $onlinemeeting->id) {
            $label = 'updatebutton';
            $url->param('update', $onlinemeeting->id);
        } else {
            $label = 'createbutton';
            $url->param('create', 1);
        }
        
        $button = $this->single_button($url, get_string($label, 'teamsmeeting'), 'post', array('target'=>'_blank'));  
        
        return  $this->container($forgroupname . $button, ' actionbutton ');  
    }
    
    
    /**
     * Prints a link to the Group dates page.
     * @param  int $cmid the course module ID
     * @return string
     */
    public function show_manage_overrides_link($cmid) {
        global $CFG;
    
        $url = new moodle_url($CFG->wwwroot.'/mod/teamsmeeting/overrides.php', array('id'=>$cmid));
        $link = $this->action_link($url, get_string('overrideslink', 'teamsmeeting'));   
        
        return $this->container(get_string('overridesexplain', 'teamsmeeting', $link), ' overrideslink ');
    }    
    
    /**
     * Prints the group-adjusted dates .
     * @param  stdClass $onlinemeeting and object with opening & closing times
     * @return string
     */
    public function show_dates($onlinemeeting) {
        
        $now = time();
        
        $result = array();
        if ($now < $onlinemeeting->openingtime) {
            $result[] = get_string('meetingnotavailable', 'teamsmeeting',
                            userdate($onlinemeeting->openingtime));
            if ($onlinemeeting->closingtime) {
                $result[] = get_string('meetingcloseson', 'teamsmeeting', userdate($onlinemeeting->closingtime));
            }

        } else if ($onlinemeeting->closingtime && $now > $onlinemeeting->closingtime) {
            $result[] = get_string('meetingclosed', 'teamsmeeting', userdate($onlinemeeting->closingtime));

        } else {
            if ($onlinemeeting->openingtime) {
                $result[] = get_string('meetingopenedon', 'teamsmeeting', userdate($onlinemeeting->openingtime));
            }
            if ($onlinemeeting->closingtime) {
                $result[] = get_string('meetingcloseson', 'teamsmeeting', userdate($onlinemeeting->closingtime));
            }
        }

        $output = '';
        if($result) {
            foreach($result as $text) {
                $output .= html_writer::tag('p', $text) . "\n";
            }
            $output = $this->box($output, 'datesinfo');
        }
        
        return $output;
    }
    
    /**
     * Prints a table of available meeting recordings
     * @param  array $recordings a collection of records forn recordings table
     * @param  bool $canmanage wether the user has capability to manage recordings
     * @return string
     */
    public function show_recordings_table($onlinemeeting, $canmanage = false) {    
        global $DB;
        
        $selectparams = ['onlinemeetingid' => $onlinemeeting->id];
        if(!$canmanage) {
            $selectparams['visible'] = 1;
        }    
    
        $totalcount = $DB->count_records('teamsmeeting_recording', $selectparams);
    
        if(!$totalcount) {
            return;
        }
    
        echo $this->heading(get_string('recordingstitle', 'teamsmeeting'), 3, null);

        $perpage = 25;
    
        if((int)$perpage < 10) {
            $perpage = 10;
        }
        
        $cmid = $this->page->cm->id;
        $params = array('id'=>$cmid, 'p'=>$perpage);
        if($onlinemeeting->groupid) {
            $params['groupid'] =  $onlinemeeting->groupid;
        }
        
        $baseurl = new moodle_url('/mod/teamsmeeting/view.php', $params);
        $manageurl = new moodle_url('/mod/teamsmeeting/recording.php', $params);
        $manageurl->param('oid', $onlinemeeting->id);
        $playurl = new moodle_url('/mod/teamsmeeting/join.php', 
                                    ['id' => $cmid, 'action' => 'view']);

        $table = new \flexible_table('teamsmeeting-recording-edit-'.$cmid);
        
        $tableheaders = ['streamurl'    => get_string('recording', 'teamsmeeting'), 
                         'name'         => get_string('recordingname', 'teamsmeeting'), 
                         'timemodified' => get_string('date'), 
                         'action'       => get_string('action'), ];
        
        if(!$canmanage) {
            unset($tableheaders['action']);
        }
                                
        $table->define_columns(array_keys($tableheaders));
        $table->define_headers(array_values($tableheaders));
        $table->define_baseurl($baseurl->out(false));
        $table->sortable(true, 'timemodified', SORT_DESC);
        $table->no_sorting('streamurl');
        if(isset($tableheaders['action'])) {
            $table->no_sorting('action');
        }
        
        $table->set_attribute('id', 'teamsmeeting_recordings');
        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('class', 'flexible generaltable recordingstable');

        $table->setup();
        $table->initialbars(false);
        $table->pagesize($perpage, $totalcount);

        if ($table->get_sql_sort()) {
            $sort = $table->get_sql_sort();
        } else {
            $sort = ' timecreated DESC, name ASC ';
        }

        $stredit   = get_string('edit');
        $strdelete = get_string('delete');
        $strview = get_string('viewinstream', 'teamsmeeting');

        if($elements = $DB->get_records('teamsmeeting_recording', $selectparams, $sort, '*', $table->get_page_start(), $table->get_page_size())) {
            foreach($elements as $element) {
                $data = [];
                $rowclass = ($element->visible) ? '' : 'dimmed';
                $playurl->params(['rid' => $element->id]);
                $data[] = \html_writer::link($playurl, get_string('recording', 'teamsmeeting'), 
                                                ['class' => 'btn btn-secondary '.$rowclass]);
                
                $data[] = \html_writer::span(format_string($element->name), $rowclass);
                //$recodingdate = userdate($element->timecreated, get_string('strftimedatetimeshort'));
                $recodingdate = userdate($element->timecreated);
                if($element->timecreated != $element->timemodified) {
                    $recodingdate .= '<br />';
                    $recodingdate .= get_string('recordingmodified', 'teamsmeeting', 
                                        userdate($element->timemodified, get_string('strftimedatetimeshort')));
                }
                $data[] = \html_writer::span($recodingdate, $rowclass);

                if(isset($tableheaders['action'])) {
                    $buttons = [];
                    $manageurl->param('item', $element->id);
                    $manageurl->param('action', 'edit');
                    $icon = new pix_icon('t/edit', $stredit, 'core', ['class'=>'iconsmall', 'title'=>$stredit]);
                    $buttons[] = $this->action_icon($manageurl, $icon);
                    
                    $manageurl->param('action', 'vis');
                    $icon = ($element->visible) ? 'show' : 'hide';
                    $title = get_string($icon);
                    $icon = new pix_icon('t/'.$icon, $title, 'core', ['class'=>'iconsmall', 'title'=>$title]);
                    $buttons[] = $this->action_icon($manageurl, $icon);
                    
                    $manageurl->param('action', 'del');
                    $icon = new pix_icon('t/delete', $strdelete, 'core', ['class'=>'iconsmall', 'title'=>$strdelete]);
                    $confirmaction = new \confirm_action(get_string('confirmrecordingdelete', 'teamsmeeting', $element->name));
                    $buttons[] = $this->action_icon($manageurl, $icon, $confirmaction);
                    
                    $icon = new pix_icon('i/messagecontentvideo', $strview, 'core', ['class'=>'iconsmall', 'title'=>$strview]);
                    $buttons[] = $this->action_icon($element->streamurl, $icon);
                    
                    
                    $data[] = $action = implode('&nbsp;&nbsp;', $buttons);                
                }


                $table->add_data($data);
            }
        }
        
        $table->finish_output();    
    }
    
    /**
     * Prints a button to add new recordings
     * 
     * @param  object $onlinemeeting record
     * @param  string $forgroupname the group name 
     * @return string
     */
    public function add_recording_button($onlinemeeting, $forgroupname = '') {    
        global $CFG;
    
        $url = new moodle_url($CFG->wwwroot.'/mod/teamsmeeting/recording.php', 
                                ['id'=>$this->page->cm->id, 'oid' => $onlinemeeting->id]);
        $url->param('action', 'add');
    
        $button = $this->single_button($url, get_string('addrecording', 'teamsmeeting'));   
        
        return  $this->container($forgroupname . $button, ' actionbutton ');  
    }
    
    
    /**
     * Bulid and iframe to contain MS-Stream url player
     * 
     * @param  url $$meetingurl collection of records forn recordings table
     * @return string
     */
    public function show_player_iframe($meetingurl) {    
        $output = '';
    
//https://web.microsoftstream.com/embed/video/777874ea-6228-4242-8f5f-ce519809bba8?autoplay=false&showinfo=true" allowfullscreen style="border:none;
//<iframe width="640" height="360" src=""></iframe>

        $url = strstr($meetingurl, '?', true);
        $params = 'autoplay=false&showinfo=true';
        $source = $url.'?'.$params;
    
        $output = '<iframe width="640" height="360" src="'.$source.'" allowfullscreen style="border:none;" >';
        $output .= '</iframe>';
    
        return $output;
    }
}
