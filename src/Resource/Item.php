<?php

namespace Art4\JsonApiClient\Resource;

use Art4\JsonApiClient\AccessInterface;
use Art4\JsonApiClient\Utils\AccessTrait;
use Art4\JsonApiClient\Utils\DataContainer;
use Art4\JsonApiClient\Utils\FactoryManagerInterface;
use Art4\JsonApiClient\Exception\AccessException;
use Art4\JsonApiClient\Exception\ValidationException;

/**
 * Resource Object
 *
 * @see http://jsonapi.org/format/#document-resource-objects
 */
final class Item implements ItemInterface, ResourceInterface
{
	use AccessTrait;

	/**
	 * @var DataContainerInterface
	 */
	protected $container;

	/**
	 * Sets the manager and parent
	 *
	 * @param FactoryManagerInterface $manager The manager
	 * @param AccessInterface $parent The parent
	 */
	public function __construct(FactoryManagerInterface $manager, AccessInterface $parent)
	{
		$this->manager = $manager;

		$this->container = new DataContainer();
	}

	/**
	 * Parses the data for this element
	 *
	 * @param mixed $object The data
	 *
	 * @return self
	 *
	 * @throws ValidationException
	 */
	public function parse($object)
	{
		if ( ! is_object($object) )
		{
			throw new ValidationException('Resource has to be an object, "' . gettype($object) . '" given.');
		}

		if ( ! property_exists($object, 'type') )
		{
			throw new ValidationException('A resource object MUST contain a type');
		}

		if ( ! property_exists($object, 'id') )
		{
			throw new ValidationException('A resource object MUST contain an id');
		}

		if ( is_object($object->type) or is_array($object->type)  )
		{
			throw new ValidationException('Resource type cannot be an array or object');
		}

		if ( is_object($object->id) or is_array($object->id)  )
		{
			throw new ValidationException('Resource id cannot be an array or object');
		}

		$this->container->set('type', strval($object->type));
		$this->container->set('id', strval($object->id));

		if ( property_exists($object, 'meta') )
		{
			$this->container->set('meta', $this->manager->getFactory()->make(
				'Meta',
				[$object->meta, $this->manager]
			));
		}

		if ( property_exists($object, 'attributes') )
		{
			$attributes = $this->manager->getFactory()->make(
				'Attributes',
				[$this->manager]
			);
			$attributes->parse($object->attributes);

			$this->container->set('attributes', $attributes);
		}

		if ( property_exists($object, 'relationships') )
		{
			$relationships = $this->manager->getFactory()->make(
				'RelationshipCollection',
				[$this->manager, $this]
			);
			$relationships->parse($object->relationships);

			$this->container->set('relationships', $relationships);
		}

		if ( property_exists($object, 'links') )
		{
			$link = $this->manager->getFactory()->make(
				'Resource\ItemLink',
				[$this->manager, $this]
			);
			$link->parse($object->links);

			$this->container->set('links', $link);
		}

		return $this;
	}

	/**
	 * Get a value by the key of this object
	 *
	 * @param string $key The key of the value
	 * @return mixed The value
	 */
	public function get($key)
	{
		try
		{
			return $this->container->get($key);
		}
		catch (AccessException $e)
		{
			throw new AccessException('"' . $key . '" doesn\'t exist in this resource.');
		}
	}

	/**
	 * Is this Resource a null resource?
	 *
	 * @return boolean false
	 */
	public function isNull()
	{
		return false;
	}

	/**
	 * Is this Resource an identifier?
	 *
	 * @return boolean false
	 */
	public function isIdentifier()
	{
		return false;
	}

	/**
	 * Is this Resource an item?
	 *
	 * @return boolean true
	 */
	public function isItem()
	{
		return true;
	}

	/**
	 * Is this Resource a collection?
	 *
	 * @return boolean false
	 */
	public function isCollection()
	{
		return false;
	}
}
