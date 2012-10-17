# Example Setup

```php
<?php
use \TYPO3\Surf\Domain\Model\Workflow;
use \TYPO3\Surf\Domain\Model\Node;
use \TYPO3\Surf\Domain\Model\SimpleWorkflow;

$application = new \Famelo\Surf\SharedHosting\Application\Flow();
$application->setOption('repositoryUrl', 'git://git.typo3.org/FLOW3/Distributions/Base.git');
$application->setDeploymentPath('/foo/bar/');
$application->setOption('keepReleases', 20);

$application->setOption('composerCommandPath', '/foo/bar/composer.phar');

// Specify the hosting package
$application->setOption('hosting', 'DomainFactory/ManagedHosting');

// Set the default context
$application->setOption('defaultContext', 'Production');

$deployment->addApplication($application);

$workflow = new SimpleWorkflow();
$deployment->setWorkflow($workflow);

$node = new Node('example.com');
$node->setHostname('example.com');
$node->setOption('username', 'my-user');

$application->addNode($node);
$deployment->addApplication($application);
?>
```