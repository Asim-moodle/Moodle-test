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
 * CSV upload form for local_csvemail plugin.
 *
 * @package    local_csvemail
 * @copyright  2024 Asim Khan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_csvemail\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form class for uploading CSV.
 *
 * @package    local_csvemail
 * @copyright  2024 Asim khan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class uploadcsv extends \moodleform {
    
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('filepicker', 'userfile', get_string('uploadcsv', 'local_csvemail'),
        null, ['accepted_types' => ['.csv']]);
        $mform->addRule('userfile', null, 'required', null, 'client');
        $this->add_action_buttons(true, get_string('uploadcsv', 'local_csvemail'));
    }
}
