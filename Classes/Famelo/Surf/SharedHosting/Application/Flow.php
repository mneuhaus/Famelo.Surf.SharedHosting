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
class Flow extends \TYPO3\Surf\Application\TYPO3\Flow {

	/**
	 * Constructor
	 */
	public function __construct($name = 'TYPO3 Flow') {
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
			->beforeTask('typo3.surf:composer:install', array('typo3.surf:composer:download'))
			->afterTask('typo3.surf:typo3:flow:copyconfiguration', array(
				'famelo.surf.sharedhosting:patchflow',
				#'famelo.surf.sharedhosting:patchsettings',
				'famelo.surf.sharedhosting:setdefaultcontext'
			), $this);
	}


	/**
	 * Set an option for this application instance
	 *
	 * @param string $key The option key
	 * @param mixed $value The option value
	 * @return \TYPO3\Surf\Domain\Model\Application The current instance for chaining
	 */
	public function setHosting($hosting) {
		switch ($hosting) {
			case 'DomainFactory/ManagedHosting':
				$this->setOption('phpPath', '/usr/local/bin/php5-54LATEST-CLI');
				$this->setOption('composerCommandPath', '/usr/local/bin/php5-54LATEST-CLI /kunden/350350_33330/composer.phar');
				$this->setOption('composerDownloadCommand', 'curl -s https://getcomposer.org/installer | /usr/local/bin/php5-53LATEST-CLI');
				break;

			default:
				# code...
				break;
		}
		$this->setOption('hosting', $hosting);
		return $this;
	}
}
?>