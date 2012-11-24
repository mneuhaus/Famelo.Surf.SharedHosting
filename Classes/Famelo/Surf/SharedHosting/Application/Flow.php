<?php
namespace Famelo\Surf\SharedHosting\Application;

/*                                                                        *
 * This script belongs to the Flow package "TYPO3.Surf".                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Domain\Model\Deployment;

/**
 * A Flow application template
* @TYPO3\Flow\Annotations\Proxy(false)
 */
class Flow extends \TYPO3\Surf\Application\BaseApplication {

	/**
	 * Constructor
	 */
	public function __construct($name = 'Flow') {
		parent::__construct($name);
	}

	/**
	 * Register tasks for this application
	 *
	 * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @return void
	 */
	public function registerTasks(Workflow $workflow, Deployment $deployment) {
		parent::registerTasks($workflow, $deployment);

		$workflow
			->addTask('typo3.surf:typo3:flow:createdirectories', 'initialize', $this)
			->afterTask('typo3.surf:gitcheckout', array(
				'famelo.surf.sharedhosting:patchcomposer',
				'typo3.surf:composer:install',
				'famelo.surf.sharedhosting:patchflow',
				'famelo.surf.sharedhosting:patchsettings',
				'famelo.surf.sharedhosting:setdefaultcontext',
				'typo3.surf:typo3:flow:symlinkdata',
				'typo3.surf:typo3:flow:symlinkconfiguration',
				'typo3.surf:typo3:flow:copyconfiguration'
			), $this);
			#->addTask('typo3.surf:flow3:migrate', 'migrate', $this)
			#->addTask('famelo.surf.sharedhosting:migrate', 'migrate', $this);
	}

}
?>