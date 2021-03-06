<?php
/**
 * Waiting List - Manual submethod search
 *
 * @package         enrol
 * @subpackage      waitinglist
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    17/08/2016
 * @author          efaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require('../../config.php');
require_once('classes/method/manual/enrolmethodmanual.php');
require_once('lib.php');

/* PARAMS */
$courseId           = required_param('course',PARAM_INT);
$instanceId         = required_param('instance',PARAM_INT);
$search             = required_param('search',PARAM_TEXT);
$selectorId         = required_param('selectorid',PARAM_ALPHANUM);
$levelThree         = optional_param('levelThree',0,PARAM_TEXT);

$optSelector    = null;
$class          = null;
$json           = array();
$groupName      = null;
$groupData      = null;

$context        = context_system::instance();
$url            = new moodle_url('/enrol/waitinglist/manualsearch.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Validate if exits the selector   */
if (!isset($USER->manual_selectors[$selectorId])) {
    print_error('unknownuserselector');
}//if_userselector

/* Get the options connected with the selector  */
$optSelector = $USER->manual_selectors[$selectorId];

/* Get Class    */
$class = $optSelector['class'];

/* GET Instance */
$instance = $DB->get_record('enrol', array('courseid' =>$courseId, 'enrol' => 'waitinglist','id' => $instanceId));
if ($instance->{ENROL_WAITINGLIST_FIELD_APPROVAL} == COMPANY_NO_DEMANDED) {
    $noDemanded = true;
}else {
    $noDemanded = false;
}
$results = enrol_waitinglist\method\manual\enrolmethodmanual::$class($instanceId,$courseId,$levelThree,$noDemanded,$search);

foreach ($results as $groupName => $manuals) {
    $groupData = array('name' => $groupName, 'users' => array());

    unset($manuals[0]);

    foreach ($manuals as $id=>$user) {
        $output     = new stdClass;
        $output->id     = $id;
        $output->name   = $user;

        if (!empty($user->disabled)) {
            $output->disabled = true;
        }
        if (!empty($user->infobelow)) {
            $output->infobelow = $user->infobelow;
        }
        $groupData['users'][$output->name] = $output;
    }

    $json[] = $groupData;
}

echo json_encode(array('results' => $json));