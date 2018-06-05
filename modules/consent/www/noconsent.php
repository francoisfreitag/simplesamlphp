<?php

/**
 * This is the page the user lands on when choosing "no" in the consent form.
 *
 * @package SimpleSAMLphp
 */

if (!array_key_exists('StateId', $_REQUEST)) {
    throw new \SimpleSAML\Error\BadRequest(
        'Missing required StateId query parameter.'
    );
}

$id = $_REQUEST['StateId'];
$state = \SimpleSAML\Auth\State::loadState($id, 'consent:request');

$resumeFrom = \SimpleSAML\Module::getModuleURL(
    'consent/getconsent.php',
    array('StateId' => $id)
);

$logoutLink = \SimpleSAML\Module::getModuleURL(
    'consent/logout.php',
    array('StateId' => $id)
);

$aboutService = null;
if (!isset($state['consent:showNoConsentAboutService']) || $state['consent:showNoConsentAboutService']) {
	if (isset($state['Destination']['url.about'])) {
		$aboutService = $state['Destination']['url.about'];
	}
}

$statsInfo = array();
if (isset($state['Destination']['entityid'])) {
    $statsInfo['spEntityID'] = $state['Destination']['entityid'];
}
\SimpleSAML\Stats::log('consent:reject', $statsInfo);

if (array_key_exists('name', $state['Destination'])) {
    $dstName = $state['Destination']['name'];
} elseif (array_key_exists('OrganizationDisplayName', $state['Destination'])) {
    $dstName = $state['Destination']['OrganizationDisplayName'];
} else {
    $dstName = $state['Destination']['entityid'];
}

$globalConfig = \SimpleSAML\Configuration::getInstance();

$t = new \SimpleSAML\XHTML\Template($globalConfig, 'consent:noconsent.php');
$t->data['dstMetadata'] = $state['Destination'];
$t->data['resumeFrom'] = $resumeFrom;
$t->data['aboutService'] = $aboutService;
$t->data['logoutLink'] = $logoutLink;

$dstName = htmlspecialchars(is_array($dstName) ? $t->t($dstName) : $dstName);

$t->data['noconsent_text'] = $t->t('{consent:consent:noconsent_text}', array('SPNAME' => $dstName));
$t->data['noconsent_abort'] = $t->t('{consent:consent:abort}', array('SPNAME' => $dstName));

$t->show();
