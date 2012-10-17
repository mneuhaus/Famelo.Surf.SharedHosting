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
 * A task for copying local configuration to the application
 *
 * The configuration directory has to exist on the target release path before
 * executing this task!
 */
class SetDefaultContextTask extends BaseTask {

	/**
	 * Executes this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 * @throws \TYPO3\Surf\Exception\TaskExecutionException
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		parent::execute($node, $application, $deployment, $options);

		if ($application->hasOption('defaultContext')) {
			$code = file_get_contents(FLOW_PATH_ROOT . '/Web/index.php');
			$code = str_replace('\'Development\'', '\'' . $application->getOption('defaultContext') . '\'', $code);

			$source = $this->temporaryPath . '/index.php';
			file_put_contents($source, $code);

			$destination = $deployment->getApplicationReleasePath($application) . '/Web/index.php';

			$this->copy($source, $destination);
		}
	}

}
?>