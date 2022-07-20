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
 * @package mod_teamsmeeting
 * @author Enrique Castro @ULPGC 
 * @copyright based on work by 2014 James McQuillan 
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2021 Enrique Castro )
 */

require_once(__DIR__.'/../../config.php');

// Course_module ID, or
$t = required_param('t', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_instance($t,  'teamsmeeting');
require_login($course);

$ucptitle = get_string('ucp_title', 'local_o365');
$url = new moodle_url('/mod/teamsmeeting/view.php', ['id' => $cm->id]);
if($groupid = optional_param('group', 0, PARAM_INT)) {
    $url->param('group', $groupid);
}
if($update = optional_param('update', 0, PARAM_INT)) {
    $url->param('update', $update);
}
if($create = optional_param('create', 0, PARAM_INT)) {
    $url->param('create', $create);
}

$page = new \local_o365\page\ucp($url, $ucptitle);
$page->run('connecttoken');
