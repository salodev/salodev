<?php

namespace salodev\GraphDB\Entities;

class Users extends Entities {
	
	static public function GetEntityName(): string {
		return 'user';
	}
	
	static public function GetInvolvedBoards($graphID) {
		return Boards::GetList([
			'relationFrom' => [
				'involved' => $graphID,
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
	
	static public function GetComments($graphID) {
		return Comments::GetList([
			'relationTo' => [
				'owner' => $graphID,
			],
		]);
	}
}

