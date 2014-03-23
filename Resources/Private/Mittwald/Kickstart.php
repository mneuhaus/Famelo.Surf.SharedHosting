<?php
use \TYPO3\Surf\Domain\Model\Workflow;
use \TYPO3\Surf\Domain\Model\Node;
use \TYPO3\Surf\Domain\Model\SimpleWorkflow;

$application = new \Famelo\Surf\SharedHosting\Application\Flow();
$application->setOption('repositoryUrl', '{repositoryUrl}');
$application->setDeploymentPath('{directory}');
$application->setOption('keepReleases', 3);

$application->setOption('defaultContext', 'Production');
$application->setOption('composerCommandPath', '/html/composer.phar');
$application->setHosting('Mittwald');

$application->setOption('transferMethod', 'rsync');
$application->setOption('packageMethod', 'git');
$application->setOption('updateMethod', NULL);

$deployment->addApplication($application);

$workflow = new SimpleWorkflow();
$workflow->setEnableRollback(FALSE);

$workflow
	->afterTask('typo3.surf:typo3:flow:copyconfiguration', array(
		'famelo.surf.sharedhosting:downloadbeard',
		'famelo.surf.sharedhosting:beardpatch'
	), $application);

$deployment->setWorkflow($workflow);

$node = new Node('{host}');
$node->setHostname('{host}');
$node->setOption('username', '{user}');

$application->addNode($node);
$deployment->addApplication($application);
?>