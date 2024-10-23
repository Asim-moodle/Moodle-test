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
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../config.php');
require_once($CFG->dirroot . '/local/csvemail/classes/form/uploadcsv.php');
require_login();

global $DB;
$context = context_system::instance();
require_capability('local/csvemail:uploadcsv', $context);

$PAGE->set_url(new moodle_url('/local/csvemail/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_csvemail'));

$mform = new \local_csvemail\form\uploadcsv();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/my'));
} else if ($data = $mform->get_data()) {
    $content = $mform->get_file_content('userfile');
    $fs = get_file_storage();

    if (!$fs->file_exists($context->id, 'user', 'csvenrol', 0, '/', 'History')) {
        $fs->create_directory($context->id, 'user', 'csvenrol', 0, '/History/', $USER->id);
    }

    $areafiles = $fs->get_area_files($context->id, 'user', 'csvenrol', false, "filename", false);
    $filechanges = ['filepath' => '/History/'];
    foreach ($areafiles as $areafile) {
        if ($areafile->get_filepath() == "/") {
            $fs->create_file_from_storedfile($filechanges, $areafile);
            $areafile->delete();
        }
    }

    $filename = "upload_" . date("Ymd_His") . ".csv";
    $fileinfo = [
        'contextid' => $context->id,
        'component' => 'user',
        'filearea' => 'csvenrol',
        'itemid' => 0,
        'filepath' => '/',
        'filename' => $filename,
        'userid' => $USER->id,
    ];
    $newfile = $fs->create_file_from_string($fileinfo, $content);

    $csv = array_map('str_getcsv', explode("\n", $content));

    foreach ($csv as $row) {
        if (count($row) == 3) {
            list($firstname, $lastname, $email) = $row;
            $email = trim($email);
            $user = $DB->get_record_sql("SELECT * FROM {user} WHERE email = ?", [$email]);

            if ($user) {
                $record = new stdClass();
                $record->firstname = trim($firstname);
                $record->lastname = trim($lastname);
                $record->email = trim($email);
                $record->date_sent = 0;

                $res = $DB->insert_record('csvemail_log', $record);
                queue_email_to_user($user, $res);
            }
        }
    }

    $log = "Processed CSV file with " . count($csv) . " rows.";
    $fileinfo['filename'] = "upload_" . date("Ymd_His") . "_log.txt";
    $newfile = $fs->create_file_from_string($fileinfo, $log);

    redirect(
        new moodle_url('/local/csvemail/index.php'),
        get_string('csvprocessed', 'local_csvemail'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();

/**
 * Queue email to user.
 *
 * @param stdClass $user User object.
 * @param int $res Record ID.
 */
function queue_email_to_user($user, $res) {
    global $DB;

    $admin = $DB->get_record_sql("SELECT * FROM {user} WHERE id = ?", [2]);
    $subject = 'Hello ' . $user->firstname . ', welcome!';
    $messagehtml = '<p>Dear ' . $user->firstname . ' ' . $user->lastname . ',</p>';
    $messagehtml .= '<p>This is a random email from our system.</p>';
    $messagetext = strip_tags($messagehtml);

    email_to_user($user, $admin, $subject, $messagetext, $messagehtml);

    $record = $DB->get_record('csvemail_log', ['id' => $res]);
    $record->date_sent = time();
    $DB->update_record('csvemail_log', $record);
}
