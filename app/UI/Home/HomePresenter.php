<?php

declare(strict_types=1);


// The primary presenter for the Fifteen game
class HomePresenter extends Nette\Application\UI\Presenter
{
	// Refreshes the 'round' part of the game view
	public function renderDefault(): void
	{
		$this->redrawControl('round');
	}


	/**
	 * Factory method to create the Fifteen game component.
	 * This component is responsible for the game's logic and rendering.
	 */
	protected function createComponentFifteen(): FifteenControl
	{
		$fifteen = new FifteenControl;
		$fifteen->onGameOver[] = $this->gameOver(...);
		$fifteen->redrawControl();
		return $fifteen;
	}


	// Event handler for the game's end state
	private function gameOver($sender, int $round): void
	{
		$this->template->flash = 'Congratulations!';
		$this->redrawControl('flash');
	}
}
