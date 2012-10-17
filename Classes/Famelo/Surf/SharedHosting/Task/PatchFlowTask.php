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
class PatchFlowTask extends BaseTask {

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

		$settings = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->resourcePath . '/Settings.yaml'));
		if (isset($settings['TYPO3']['Flow']['core']['phpBinaryPathAndFilename'])) {
			$lines = file(FLOW_PATH_ROOT . '/flow');
			$lines[0] = '#!' . $settings['TYPO3']['Flow']['core']['phpBinaryPathAndFilename'];

			$source = $this->temporaryPath . '/flow';
			file_put_contents($source, implode("\n", $lines));

			$destination = $deployment->getApplicationReleasePath($application) . '/flow';

			$this->copy($source, $destination);
		}
	}

}
?>