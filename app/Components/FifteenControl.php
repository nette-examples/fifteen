<?php

declare(strict_types=1);

use Nette\Application\Attributes\Persistent;
use Nette\Application\UI;
use Nette\Utils\Arrays;

/**
 * The control responsible for the Fifteen game logic and presentation.
 */
class FifteenControl extends UI\Control
{
	// Game's grid dimensions
	public int $width = 4;

	// Event triggered after a valid game move
	public array $onAfterClick = [];

	// Event triggered when the game is completed
	public array $onGameOver = [];

	// Persistent game state: order of tiles and current round
	#[Persistent]
	public array $order = [];

	#[Persistent]
	public int $round = 0;


	public function __construct()
	{
		$this->order = range(0, $this->width * $this->width - 1);
	}


	// Handle tile click events in the game
	public function handleClick(int $x, int $y): void
	{
		// Ensure the move is valid
		if (!$this->isClickable($x, $y)) {
			throw new UI\BadSignalException('Action not allowed.');
		}

		// Execute the move and update the game state
		$this->move($x, $y);
		$this->round++;
		Arrays::invoke($this->onAfterClick, $this);

		// Check for game completion
		if ($this->order == range(0, $this->width * $this->width - 1)) {
			Arrays::invoke($this->onGameOver, $this, $this->round);
		}
	}


	// Randomly shuffle the game tiles
	public function handleShuffle(): void
	{
		$i = 100;
		while ($i) {
			$x = rand(0, $this->width - 1);
			$y = rand(0, $this->width - 1);
			if ($this->isClickable($x, $y)) {
				$this->move($x, $y);
				$i--;
			}
		}
		$this->round = 0;
	}


	public function getRound(): int
	{
		return $this->round;
	}


	// Check if a tile can be moved to the empty slot
	public function isClickable(int $x, int $y, string &$rel = null): bool
	{
		$rel = null;
		$pos = $x + $y * $this->width;
		$empty = $this->searchEmpty();
		$y = (int) ($empty / $this->width);
		$x = $empty % $this->width;
		if ($x > 0 && $pos === $empty - 1) {
			$rel = '-1,';
			return true;
		}
		if ($x < $this->width - 1 && $pos === $empty + 1) {
			$rel = '+1,';
			return true;
		}
		if ($y > 0 && $pos === $empty - $this->width) {
			$rel = ',-1';
			return true;
		}
		if ($y < $this->width - 1 && $pos === $empty + $this->width) {
			$rel = ',+1';
			return true;
		}
		return false;
	}


	// Move the clicked tile to the empty slot
	private function move(int $x, int $y): void
	{
		$pos = $x + $y * $this->width;
		$emptyPos = $this->searchEmpty();
		$this->order[$emptyPos] = $this->order[$pos];
		$this->order[$pos] = 0;
	}


	// Find the position of the empty slot in the game grid
	private function searchEmpty(): int
	{
		return array_search(0, $this->order, true);
	}


	// Render the game's current state using its template
	public function render(): void
	{
		$template = $this->template;
		$template->width = $this->width;
		$template->order = $this->order;
		$template->render(__DIR__ . '/FifteenControl.latte');
	}


	// Load saved game state from request
	public function loadState(array $params): void
	{
		if (isset($params['order'])) {
			$params['order'] = array_map('intval', explode('.', (string) $params['order']));

			// validate
			$copy = $params['order'];
			sort($copy);
			if ($copy != range(0, $this->width * $this->width - 1)) {
				unset($params['order']);
			}
		}

		parent::loadState($params);
	}


	// Save the current game state to HTTP URL
	public function saveState(array &$params): void
	{
		parent::saveState($params);
		if (isset($params['order'])) {
			$params['order'] = implode('.', $params['order']);
		}
	}
}
