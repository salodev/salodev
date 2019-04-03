<?php
namespace salodev;

use Exception;
use stdClass;

class EventsHandler {

	private $_events = [];

	public function addListener(string $eventName, callable $eventListener, bool $persistent = true): self {

		if (!is_callable($eventListener)) {
			throw new Exception('eventListener must be a function');
		}

		$evData = new stdClass();
		$evData->eventListener = $eventListener;
		$evData->persistent    = (bool) $persistent;

		$this->_events[$eventName][] = $evData;

		return $this;
	}

	public function removeListeners(string $eventName): self {
		if (isset($this->_events[$eventName])) {
			unset($this->_events[$eventName]);
		}
		return $this;
	}

	public function trigger(string $eventName, $source, array $params = []): bool {
		if(isset($this->_events[$eventName])) {			
			foreach ($this->_events[$eventName] as $k => $evData) {
				$eventListener = $evData->eventListener;
				$return = $eventListener($params, $source);
				if (!$evData->persistent) {
					unset($this->_events[$eventName][$k]);
				}

				// Si un eventListener response con FALSE, significa que está solicitando
				// la detención de ejecución de los eventos, y dicho valor será entregado
				// como resultado a quien ejecuta el método trigger, para que éste decida
				// cómo actuar en ese caso.
				if ($return === false) {
					return false;
				}
			}			
		}
		
		return true;
	}
}