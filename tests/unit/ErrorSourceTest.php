<?php
/*
 * A PHP Library to handle a JSON API body in an OOP way.
 * Copyright (C) 2015-2016  Artur Weigandt  https://wlabs.de/kontakt

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Art4\JsonApiClient\Tests;

use Art4\JsonApiClient\ErrorSource;
use Art4\JsonApiClient\Tests\Fixtures\HelperTrait;

class ErrorSourceTest extends \PHPUnit_Framework_TestCase
{
	use HelperTrait;

	/**
	 * @setup
	 */
	public function setUp()
	{
		$this->manager = $this->buildManagerMock();
	}

	/**
	 * @test only 'about' property' can exist
	 *
	 * source: an object containing references to the source of the error, optionally including any of the following members:
	 * - pointer: a JSON Pointer [RFC6901] to the associated entity in the request document [e.g. "/data" for a primary data object, or "/data/attributes/title" for a specific attribute].
	 * - parameter: a string indicating which query parameter caused the error.
	 */
	public function testOnlyPointerParameterPropertiesExists()
	{
		$object = new \stdClass();
		$object->pointer = '/pointer';
		$object->parameter = 'parameter';
		$object->ignore = 'must be ignored';

		$source = new ErrorSource($this->manager, $this->getMock('Art4\JsonApiClient\AccessInterface'));
		$source->parse($object);

		$this->assertInstanceOf('Art4\JsonApiClient\ErrorSource', $source);
		$this->assertInstanceOf('Art4\JsonApiClient\AccessInterface', $source);
		$this->assertSame($source->getKeys(), array('pointer', 'parameter'));

		$this->assertFalse($source->has('ignore'));
		$this->assertTrue($source->has('pointer'));
		$this->assertSame($source->get('pointer'), '/pointer');
		$this->assertTrue($source->has('parameter'));
		$this->assertSame($source->get('parameter'), 'parameter');

		$this->assertSame($source->asArray(), array(
			'pointer' => $source->get('pointer'),
			'parameter' => $source->get('parameter'),
		));

		// Test full array
		$this->assertSame($source->asArray(true), array(
			'pointer' => $source->get('pointer'),
			'parameter' => $source->get('parameter'),
		));

		// test get() with not existing key throws an exception
		$this->assertFalse($source->has('something'));

		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\AccessException',
			'"something" doesn\'t exist in this error source.'
		);

		$source->get('something');
	}

	/**
	 * @dataProvider jsonValuesProvider
	 *
	 * source: an object containing references to ...
	 */
	public function testCreateWithoutObjectThrowsException($input)
	{
		// Input must be an object
		if ( gettype($input) === 'object' )
		{
			return;
		}

		$source = new ErrorSource($this->manager, $this->getMock('Art4\JsonApiClient\AccessInterface'));

		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\ValidationException',
			'ErrorSource has to be an object, "' . gettype($input) . '" given.'
		);

		$source->parse($input);
	}

	/**
	 * @dataProvider jsonValuesProvider
	 *
	 * pointer: a JSON Pointer [RFC6901] to the associated entity in the request document [e.g. "/data" for a primary data object, or "/data/attributes/title" for a specific attribute].
	 */
	public function testPointerMustBeAString($input)
	{
		// Input must be a string
		if ( gettype($input) === 'string' )
		{
			return;
		}

		$object = new \stdClass();
		$object->pointer = $input;

		$source = new ErrorSource($this->manager, $this->getMock('Art4\JsonApiClient\AccessInterface'));

		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\ValidationException',
			'property "pointer" has to be a string, "' . gettype($input) . '" given.'
		);

		$source->parse($object);
	}

	/**
	 * @dataProvider jsonValuesProvider
	 *
	 * parameter: a string indicating which query parameter caused the error.
	 */
	public function testParameterMustBeAString($input)
	{
		// Input must be a string
		if ( gettype($input) === 'string' )
		{
			return;
		}

		$object = new \stdClass();
		$object->parameter = $input;

		$source = new ErrorSource($this->manager, $this->getMock('Art4\JsonApiClient\AccessInterface'));

		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\ValidationException',
			'property "parameter" has to be a string, "' . gettype($input) . '" given.'
		);

		$source->parse($object);
	}
}
