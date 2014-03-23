<?php
namespace Famelo\Surf\SharedHosting\Task;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;

/**
 * Downloads composer into the current releasePath.
 */
class BeardPatchTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @throws \TYPO3\Surf\Exception\TaskExecutionException
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {

		if ($application->getOption('transferMethod') == 'rsync') {
			$path = $deployment->getWorkspacePath($application);
			$node = $deployment->getNode('localhost');
			$command = 'beard patch';
		} else {
			$patch = $deployment->getApplicationReleasePath($application);
			$command = $application->getOption('phpPath') . ' beard.phar patch';
		}

		$command = sprintf('cd %s && %s', escapeshellarg($path), $command);
		$this->shell->executeOrSimulate($command, $node, $deployment);
	}
}

?>