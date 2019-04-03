<?php


class Tasks extends Entities {
	static public function GetEntityName(): string {
		return 'task';
	}
}

class Boards extends Entities {
	static public function GetEntityName(): string {
		return 'board';
	}
	
	static public function GetUsersInvolved($graphID) {
		return Users::GetList([
			'relationTo' => [
				'involved' => $graphID,
			],
		]);
	}
}

class Relation {
	public function type(string $type): self { }
	public function name(string $name): self { }
	public function graphType(string $name): self { }
	public function graphID(int $graphID): self { }
	public function graphIDs(array $graphIDs): self { }
	public function data(string $key, string $value): self { }
}
class Data {
	public function name(string $name): self { }
	public function value(string $value): self { }
}

class QueryGraph {
	static public function Get() {
		return new Get;
	}
	
	static public function Add() {
		return new Add;
	}
}

class Get {
	public function relation($type): Relation {
		$r = new Relation;
		$r->type($type);
		$this->_relations[] = $r;
		return $r;
	}
	public function relationTo(): Relation {
		return $this->relation('to');
	}
	public function relationFrom(): Relation {
		return $this->relation('from');
	}
	public function _or(): QueryGraph {}
	
	public function type(string $type): self {}
	public function data(): Data {}
	public function offset(int $offset): self {}
	public function limit(int $limit): self {}
}

class Add {
	
}

$q = QueryGraph::Get();
$q->type('user');
$q->data()->name('role')->value('user');
$q->relationTo()->name('involved')->graphType('board')->data('from', '> 2015');
$q->relationTo()->name('owner')->graphType('comment');
$q->getData();