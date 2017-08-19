<?php
namespace salodev;

class EventsHandler {

	private $_events = array();

	public function addListener($eventName, callable $eventListener, $persistent = true){

		if (!is_callable($eventListener)) {
			throw new \Exception('eventListener must be a function');
		}

		$evData = new \stdClass();
		$evData->eventListener = $eventListener;
		$evData->persistent    = (bool) $persistent;

		$this->_events[$eventName][] = $evData;

		return $this;
	}

	public function removeListeners($eventName){
		//@todo: completar esto..
		throw new \Exception('TODO: complete it.');
	}

	public function trigger($eventName, $source, $params = null) {
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