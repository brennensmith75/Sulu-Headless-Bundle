<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\HeadlessBundle\Content;

use Sulu\Bundle\HeadlessBundle\Content\Resolver\ContentTypeResolverInterface;
use Sulu\Component\Content\Compat\PropertyInterface;

class ContentResolver implements ContentResolverInterface
{
    /**
     * @var ContentTypeResolverInterface[]
     */
    private $resolvers;

    /**
     * @param ContentTypeResolverInterface[] $resolvers
     */
    public function __construct(array $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function resolve($data, PropertyInterface $property, string $locale, array $attributes = []): ContentView
    {
        if (!\array_key_exists($property->getContentTypeName(), $this->resolvers)) {
            return new ContentView($data);
        }

        return $this->resolvers[$property->getContentTypeName()]->resolve($data, $property, $locale, $attributes);
    }
}
