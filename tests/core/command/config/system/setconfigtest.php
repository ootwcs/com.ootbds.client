<?php
/**
 * @author Joas Schilling <nickvergessen@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace Tests\Core\Command\Config\System;


use OC\Core\Command\Config\System\SetConfig;
use Test\TestCase;

class SetConfigTest extends TestCase {
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $systemConfig;

	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $consoleInput;
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $consoleOutput;

	/** @var \Symfony\Component\Console\Command\Command */
	protected $command;

	protected function setUp() {
		parent::setUp();

		$systemConfig = $this->systemConfig = $this->getMockBuilder('OC\SystemConfig')
			->disableOriginalConstructor()
			->getMock();
		$this->consoleInput = $this->getMock('Symfony\Component\Console\Input\InputInterface');
		$this->consoleOutput = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

		/** @var \OC\SystemConfig $systemConfig */
		$this->command = new SetConfig($systemConfig);
	}


	public function setData() {
		return [
			[
				'name',
				'newvalue',
				true,
				true,
				true,
				'info',
			],
			[
				'name',
				'newvalue',
				false,
				true,
				false,
				'comment',
			],
		];
	}

	/**
	 * @dataProvider setData
	 *
	 * @param string $configName
	 * @param mixed $newValue
	 * @param bool $configExists
	 * @param bool $updateOnly
	 * @param bool $updated
	 * @param string $expectedMessage
	 */
	public function testSet($configName, $newValue, $configExists, $updateOnly, $updated, $expectedMessage) {
		$this->systemConfig->expects($this->once())
			->method('getKeys')
			->willReturn($configExists ? [$configName] : []);

		if ($updated) {
			$this->systemConfig->expects($this->once())
				->method('setValue')
				->with($configName, $newValue);
		}

		$this->consoleInput->expects($this->once())
			->method('getArgument')
			->with('name')
			->willReturn($configName);
		$this->consoleInput->expects($this->any())
			->method('getOption')
			->with('value')
			->willReturn($newValue);
		$this->consoleInput->expects($this->any())
			->method('hasParameterOption')
			->with('--update-only')
			->willReturn($updateOnly);

		$this->consoleOutput->expects($this->any())
			->method('writeln')
			->with($this->stringContains($expectedMessage));

		\Test_Helper::invokePrivate($this->command, 'execute', [$this->consoleInput, $this->consoleOutput]);
	}
}