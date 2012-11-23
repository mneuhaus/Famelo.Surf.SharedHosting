<?php
namespace Famelo\Surf\SharedHosting\Task;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;

/**
 * A FLOW3 migration task
 *
 */
class MigrateTask extends \TYPO3\Surf\Task\TYPO3\Flow\MigrateTask {

	/**
	 * Execute this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$targetPath = $deployment->getApplicationReleasePath($application);
		$context = $application->hasOption('defaultContext') ? $application->getOption('defaultContext') : 'Production';
		$this->shell->executeOrSimulate('cd ' . $targetPath . ' && FLOW_CONTEXT=' . $context . ' ./flow typo3.flow:doctrine:migrate', $node, $deployment);
	}

}
?>