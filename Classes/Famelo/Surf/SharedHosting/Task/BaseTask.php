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
class BaseTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 * @Flow\Inject
	 */
	protected $packageManager;

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
		$this->hosting = $application->getOption('hosting');
		$this->username = $options['username'];
		$this->hostname = $node->getHostname();
		$this->deployment = $deployment;

		$this->resourcePath = $this->packageManager->getPackage('Famelo.Surf.SharedHosting')->getResourcesPath() . 'Private/' . $this->hosting;

		$this->temporaryPath = FLOW_PATH_ROOT . '/Data/Temporary/Deployment/' . $this->hosting;
		if (!is_dir($this->temporaryPath)) {
			\TYPO3\Flow\Utility\Files::createDirectoryRecursively($this->temporaryPath);
		}
	}

	/**
	 * Simulate this task
	 *
	 * @param Node $node
	 * @param Application $application
	 * @param Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$this->execute($node, $application, $deployment, $options);
	}

	public function copy($source, $destination) {
		$localhost = new Node('localhost');
		$localhost->setHostname('localhost');

		$this->shell->executeOrSimulate(array(
			'scp ' . $source . ' ' . $this->username . '@' . $this->hostname . ':' . $destination
		), $localhost, $this->deployment);
	}

}
?>