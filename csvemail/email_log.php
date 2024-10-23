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
 *  local_csvemail plugin.
 *
 * @package    local_csvemail
 * @copyright  2024 Asim Khan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_login();

$PAGE->set_url(new moodle_url('/local/csvemail/email_log.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Sent Emails");
$PAGE->set_heading("Sent Emails Log");

$page = optional_param('page', 0, PARAM_INT);
$perpage = 10;

global $DB;
$totalcount = $DB->count_records('csvemail_log');

$baseurl = new moodle_url('/local/csvemail/email_log.php');
$pagination = new paging_bar($totalcount, $page, $perpage, $baseurl);

$users = $DB->get_records('csvemail_log', null, 'date_sent DESC', '*', $page * $perpage, $perpage);


$table = new html_table();

$table->head = ['First Name', 'Last Name', 'Email', 'Date Sent'];


foreach ($users as $user) {
    $table->data[] = [
        $user->firstname,
        $user->lastname,
        $user->email,
        date('d/m/Y H:i', $user->date_sent),
    ];
}

echo $OUTPUT->header();


echo html_writer::table($table);


echo $OUTPUT->render($pagination);

echo $OUTPUT->footer();
