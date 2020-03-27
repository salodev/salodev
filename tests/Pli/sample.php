#!/usr/bin/php
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');


try {
	$ce = new \salodev\Pli\ComputingEngine(salodev\Pli\CustomLang\Tokens\Code::class);
	$result = $ce->evaluate(file_get_contents(dirname(__FILE__) . '/sample1.code'));
	
	// echo "{$result}\n";
	die();
} catch (\Exception $e) {
	echo $e->getMessage() . "\n\nCode:\n";
	$ce->showCurrentParsing(300);
	// throw $e;
}
die();


class SelectQuery extends salodev\Pli\Token {
	
	public function __construct(QueryModel $queryModel) {
		$this->_queryModel = $queryModel;
	}
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		if (!$this->eatString('SELECT', false)) {
			return false;
		}
		
		if (!$evaluate) {
			$this->_queryModel->select = 'SELECT ';
		}
		
		if ($this->eatString('SQL_CALC_FOUND_ROWS') && !$evaluate) {
			$this->_queryModel->select .= 'SQL_CALC_FOUND_ROWS ';
		}
		
		$this->token($tokenClass);
		
	}

}