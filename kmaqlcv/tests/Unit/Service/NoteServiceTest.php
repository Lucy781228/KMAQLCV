<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Lucy <ct040407@actv.edu.vn>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\QLCV\Tests\Unit\Service;

use OCA\QLCV\Db\Note;
use OCA\QLCV\Db\NoteMapper;

use OCA\QLCV\Service\NoteNotFound;

use OCA\QLCV\Service\NoteService;
use OCP\AppFramework\Db\DoesNotExistException;
use PHPUnit\Framework\TestCase;

class NoteServiceTest extends TestCase {
	private NoteService $service;
	private string $userId = 'john';
	private $mapper;

	public function setUp(): void {
		$this->mapper = $this->getMockBuilder(NoteMapper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->service = new NoteService($this->mapper);
	}

	public function testUpdate(): void {
		// the existing note
		$note = Note::fromRow([
			'id' => 3,
			'title' => 'yo',
			'content' => 'nope'
		]);
		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo(3))
			->will($this->returnValue($note));

		// the note when updated
		$updatedNote = Note::fromRow(['id' => 3]);
		$updatedNote->setTitle('title');
		$updatedNote->setContent('content');
		$this->mapper->expects($this->once())
			->method('update')
			->with($this->equalTo($updatedNote))
			->will($this->returnValue($updatedNote));

		$result = $this->service->update(3, 'title', 'content', $this->userId);

		$this->assertEquals($updatedNote, $result);
	}

	public function testUpdateNotFound(): void {
		$this->expectException(NoteNotFound::class);
		// test the correct status code if no note is found
		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo(3))
			->will($this->throwException(new DoesNotExistException('')));

		$this->service->update(3, 'title', 'content', $this->userId);
	}
}
