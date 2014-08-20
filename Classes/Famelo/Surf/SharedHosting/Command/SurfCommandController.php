<?php
namespace Famelo\Surf\SharedHosting\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use Famelo\Common\Command\AbstractInteractiveCommandController;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;

/**
 * Surf command controller
 */
class SurfCommandController extends AbstractInteractiveCommandController {

	/**
	 * @var array
	 * @Flow\Inject(setting="templates")
	 */
	protected $templates;

	/**
	 * @var array
	 */
	protected $template;

	/**
	 * @var string
	 */
	protected $repositoryUrl;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $host;

	/**
	 * @var string
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $directory;

	/**
	 * @var integer
	 */
	protected $attempts = 0;

	/**
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 * @Flow\Inject
	 */
	protected $packageManager;

	/**
	 * Setup a new Deployment
	 *
	 * @return void
	 */
	public function kickstartCommand() {
		$this->repositoryUrl = $this->getRepositoryUrl();
		$this->name = $this->ask('<info>Please enter a name for this deployment (Default: Live)</info>', 'live');

		$this->template = $this->getTemplate();

		while (!$this->isSshPublicKeyAuthenticationWorking()) {
			if ($this->attempts >= 3) {
				$this->outputLine('<error>Failed to setup SSH</error>');
			}
			$this->attempts++;
			$this->setupPublicKeyAuthentication();
		}

		do {
			$this->directory = $this->chooseDirectory();
			$directoryConfirmed = $this->askConfirmation('<error>Are you sure, you want to use "' . $this->directory . '"? All existing files will be deleted.</error>', FALSE);
		} while ($directoryConfirmed !== TRUE);

		if (strlen($this->directory) < 4) {
			return;
		}
		$this->createStructure();
		$this->createSurfScript();
	}

	public function getRepositoryUrl() {
		preg_match('/Fetch URL: (.*)/', shell_exec('git remote show origin'), $match);
		$repositoryUrl = NULL;
		if (isset($match[1])) {
			$repositoryUrl = $match[1];
		}
		return $this->ask('<info>Please Specify the Git Source. (Default: ' . $repositoryUrl . ')</info>' . chr(10), $repositoryUrl);
	}

	public function getTemplate() {
		$templates = array_keys($this->templates);
		$templateIndex = $this->select('<info>Please select a Template</info>', $templates);
		return $this->templates[$templates[$templateIndex]];
	}

	public function isSshPublicKeyAuthenticationWorking() {
		$this->host = $this->ask('<info>Please enter the hostname to deploy to: </info>');
		$this->user = $this->ask('<info>Please enter the SSH username for that host: </info>');
		$output = trim(shell_exec('ssh -o BatchMode=yes ' . $this->user . '@' . $this->host . ' true 2>&1; echo $?'));
		if ($output === "0") {
			return TRUE;
		}

		if (stristr($output, 'Permission denied') !== FALSE) {
			return FALSE;
		}
		return FALSE;
	}

	public function setupPublicKeyAuthentication() {
		$this->outputLine('<info>Setting up SSH Public Key Authentication</info>');
		exec('cat ~/.ssh/*.pub | ssh ' . $this->user . '@' . $this->host . ' \'umask 077; cat >>.ssh/authorized_keys\'');
	}

	public function chooseDirectory() {
		$output = trim(shell_exec('ssh ' . $this->user . '@' . $this->host . ' "ls -d ' . $this->template['htmlBase'] . '*/"'));
		$directories = explode(chr(10), $output);
		$directories['new'] = 'new directory';
		$directoryIndex = $this->select('<info>Please select the target Directory</info>', $directories);
		if ($directoryIndex == 'new') {
			$directory = $this->template['htmlBase'] . $this->ask('<info>Please enter the name of the directory to create inside "' . $this->template['htmlBase'] . '": </info>') . '/';
			$this->sshCommand('mkdir -p ' . $directory);
		} else {
			$directory = $directories[$directoryIndex];
		}
		return $directory;
	}

	public function createStructure() {
		// $this->sshCommand('mv ' . $this->directory . '/Configuration/Settings.yaml ' . $this->directory . '../Settings.yaml');
		$output = $this->sshCommand('rm -rf ' . $this->directory . '*');
		$output = $this->sshCommand('mkdir -p ' . $this->directory . 'shared/Configuration/Production');
		$this->sshCommand('cp ' . $this->directory . '../Settings.yaml ' . $this->directory . '/shared/Configuration/Production/Settings.yaml ');
		$output = $this->sshCommand('cd ' . $this->directory . ' && ln -s releases/current/Web Web');
	}

	public function sshCommand($command) {
		return trim(shell_exec('ssh ' . $this->user . '@' . $this->host . ' "' . $command . '"'));
	}

	public function createSurfScript() {
		$template = file_get_contents($this->template['resources'] . 'Kickstart.php');
		$replacements = array();
		foreach (explode(',', 'repositoryUrl,host,user,name,directory') as $key) {
			$replacements['{' . $key . '}'] = $this->$key;
		}
		$script = str_replace(array_keys($replacements), array_values($replacements), $template);
		$scriptFile = 'Build/Surf/' . $this->name . '.php';
		var_dump($scriptFile);
		if (!file_exists($scriptFile)) {
			Files::createDirectoryRecursively(dirname($scriptFile));
			file_put_contents($scriptFile, $script);
		}
	}
}
?>
