<?php

namespace salodev\GraphDB\Entities;

class Users extends Entities {
	
	static public function GetEntityName(): string {
		return 'user';
	}
	
	static public function GetInvolvedBoards($graphId) {
		return Boards::GetList([
			'relationFrom' => [
				'involved' => $graphId,
			],
		]);
	}
	
	static public function GetAdmins(): array {
		return static::GetList([
			'data' => [
				'role' => 'admin',
			],
		]);
	}
	
	static public function GetByEmail(string $email): array {
		return static::GetList([
			'data' => [
				'email' => $email,
			]
		]);
	}
	
	static public function GetByUsername(string $username): array {
		return static::GetList([
			'data' => [
				'username' => $username,
			]
		]);
	}
	
	static public function GetComments($graphId) {
		return Comments::GetList([
			'relationTo' => [
				'owner' => $graphId,
			],
		]);
	}
}

