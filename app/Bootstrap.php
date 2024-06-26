<?php

declare(strict_types=1);

namespace App;

use Nette\Application\Routers\SimpleRouter;
use Nette\Bootstrap\Configurator;


/**
 * Bootstrap class initializes application environment and DI container.
 */
class Bootstrap
{
	public static function boot(): Configurator
	{
		// The configurator is responsible for setting up the application environment and services.
		// Learn more at https://doc.nette.org/en/bootstrap
		$configurator = new Configurator;

		// Nette is smart, and the development mode turns on automatically,
		// or you can enable for a specific IP address it by uncommenting the following line:
		// $configurator->setDebugMode('secret@23.75.345.200');

		// Enables Tracy: the ultimate "swiss army knife" debugging tool.
		// Learn more about Tracy at https://tracy.nette.org
		$configurator->enableTracy(__DIR__ . '/../log');

		// Set the directory for temporary files generated by Nette (e.g. compiled templates)
		$configurator->setTempDirectory(__DIR__ . '/../temp');

		// RobotLoader: autoloads all classes in the given directory
		$configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();

		// Setup router
		$configurator->addServices(['router' => new SimpleRouter('Home:default')]);

		return $configurator;
	}
}
