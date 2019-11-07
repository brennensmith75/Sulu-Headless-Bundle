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

namespace Sulu\Bundle\HeadlessBundle\Tests\Unit\Content;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\HeadlessBundle\Content\ContentResolverInterface;
use Sulu\Bundle\HeadlessBundle\Content\ContentView;
use Sulu\Bundle\HeadlessBundle\Content\StructureResolver;
use Sulu\Bundle\PageBundle\Document\BasePageDocument;
use Sulu\Bundle\PageBundle\Document\HomeDocument;
use Sulu\Bundle\PageBundle\Document\PageDocument;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\Compat\Structure\StructureBridge;

class StructureResolverTest extends TestCase
{
    /**
     * @var StructureBridge|ObjectProphecy
     */
    private $structure;

    /**
     * @var BasePageDocument|ObjectProphecy
     */
    private $pageDocument;

    /**
     * @var BasePageDocument|ObjectProphecy
     */
    private $homepageDocument;

    /**
     * @var ContentResolverInterface|ObjectProphecy
     */
    private $contentResolver;

    /**
     * @var StructureResolver
     */
    private $structureResolver;

    protected function setUp(): void
    {
        $this->structure = $this->prophesize(StructureBridge::class);
        $this->pageDocument = $this->prophesize(PageDocument::class);
        $this->homepageDocument = $this->prophesize(HomeDocument::class);

        $this->contentResolver = $this->prophesize(ContentResolverInterface::class);

        $this->structureResolver = new StructureResolver($this->contentResolver->reveal());
    }

    public function testResolvePage(): void
    {
        $this->structure->getDocument()->willReturn($this->pageDocument->reveal());

        $now = new \DateTimeImmutable();

        $this->structure->getUuid()->willReturn('123-123-123');
        $this->structure->getWebspaceKey()->willReturn('sulu_io');
        $this->structure->getLanguageCode()->willReturn('en');

        $this->pageDocument->getStructureType()->willReturn('default');
        $this->pageDocument->getAuthored()->willReturn($now);
        $this->pageDocument->getAuthor()->willReturn(1);
        $this->pageDocument->getCreated()->willReturn($now);
        $this->pageDocument->getCreator()->willReturn(2);
        $this->pageDocument->getChanged()->willReturn($now);
        $this->pageDocument->getChanger()->willReturn(3);

        $titleProperty = $this->prophesize(PropertyInterface::class);
        $titleProperty->getName()->willReturn('title');
        $titleProperty->getValue()->willReturn('test-123');
        $mediaProperty = $this->prophesize(PropertyInterface::class);
        $mediaProperty->getName()->willReturn('media');
        $mediaProperty->getValue()->willReturn(['ids' => [1, 2, 3]]);

        $this->structure->getProperties(true)->willReturn(
            [
                $titleProperty->reveal(),
                $mediaProperty->reveal(),
            ]
        );

        $contentView1 = $this->prophesize(ContentView::class);
        $contentView1->getContent()->willReturn('test-123');
        $contentView1->getView()->willReturn([]);

        $this->contentResolver->resolve('test-123', $titleProperty->reveal(), 'en', ['webspaceKey' => 'sulu_io'])
            ->willReturn($contentView1->reveal());

        $contentView2 = $this->prophesize(ContentView::class);
        $contentView2->getContent()->willReturn(['media1', 'media2', 'media3']);
        $contentView2->getView()->willReturn(['ids' => [1, 2, 3]]);

        $this->contentResolver->resolve(
            ['ids' => [1, 2, 3]],
            $mediaProperty->reveal(),
            'en',
            ['webspaceKey' => 'sulu_io']
        )->willReturn($contentView2->reveal());

        $this->assertSame(
            [
                'id' => '123-123-123',
                'type' => 'page',
                'template' => 'default',
                'content' => [
                    'title' => 'test-123',
                    'media' => ['media1', 'media2', 'media3'],
                ],
                'view' => [
                    'title' => [],
                    'media' => ['ids' => [1, 2, 3]], ],
                'extension' => [],
                'author' => 1,
                'authored' => $now->format(\DateTimeImmutable::ISO8601),
                'changer' => 3,
                'changed' => $now->format(\DateTimeImmutable::ISO8601),
                'creator' => 2,
                'created' => $now->format(\DateTimeImmutable::ISO8601),
            ],
            $this->structureResolver->resolve($this->structure->reveal(), 'en')
        );
    }

    public function testResolveHomepage(): void
    {
        $this->structure->getDocument()->willReturn($this->homepageDocument->reveal());

        $now = new \DateTimeImmutable();

        $this->structure->getUuid()->willReturn('123-123-123');
        $this->structure->getParent()->shouldNotBeCalled();
        $this->structure->getWebspaceKey()->willReturn('sulu_io');
        $this->structure->getLanguageCode()->willReturn('en');

        $this->homepageDocument->getStructureType()->willReturn('default');
        $this->homepageDocument->getAuthored()->willReturn($now);
        $this->homepageDocument->getAuthor()->willReturn(1);
        $this->homepageDocument->getCreated()->willReturn($now);
        $this->homepageDocument->getCreator()->willReturn(2);
        $this->homepageDocument->getChanged()->willReturn($now);
        $this->homepageDocument->getChanger()->willReturn(3);

        $titleProperty = $this->prophesize(PropertyInterface::class);
        $titleProperty->getName()->willReturn('title');
        $titleProperty->getValue()->willReturn('test-123');
        $mediaProperty = $this->prophesize(PropertyInterface::class);
        $mediaProperty->getName()->willReturn('media');
        $mediaProperty->getValue()->willReturn(['ids' => [1, 2, 3]]);

        $this->structure->getProperties(true)->willReturn(
            [
                $titleProperty->reveal(),
                $mediaProperty->reveal(),
            ]
        );

        $contentView1 = $this->prophesize(ContentView::class);
        $contentView1->getContent()->willReturn('test-123');
        $contentView1->getView()->willReturn([]);

        $this->contentResolver->resolve('test-123', $titleProperty->reveal(), 'en', ['webspaceKey' => 'sulu_io'])
            ->willReturn($contentView1->reveal());

        $contentView2 = $this->prophesize(ContentView::class);
        $contentView2->getContent()->willReturn(['media1', 'media2', 'media3']);
        $contentView2->getView()->willReturn(['ids' => [1, 2, 3]]);

        $this->contentResolver->resolve(
            ['ids' => [1, 2, 3]],
            $mediaProperty->reveal(),
            'en',
            ['webspaceKey' => 'sulu_io']
        )->willReturn($contentView2->reveal());

        $this->assertSame(
            [
                'id' => '123-123-123',
                'type' => 'page',
                'template' => 'default',
                'content' => [
                    'title' => 'test-123',
                    'media' => ['media1', 'media2', 'media3'],
                ],
                'view' => [
                    'title' => [],
                    'media' => ['ids' => [1, 2, 3]], ],
                'extension' => [],
                'author' => 1,
                'authored' => $now->format(\DateTimeImmutable::ISO8601),
                'changer' => 3,
                'changed' => $now->format(\DateTimeImmutable::ISO8601),
                'creator' => 2,
                'created' => $now->format(\DateTimeImmutable::ISO8601),
            ],
            $this->structureResolver->resolve($this->structure->reveal(), 'en')
        );
    }
}
