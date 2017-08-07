<?php

namespace CommunityVoices\Model\Entity;

use PHPUnit\Framework\TestCase;
use Mock\Entity;
use \InvalidArgumentException;

class GroupCollectionTest extends TestCase
{
    public function provide_Group_Type_Assignment()
    {
        return [
            [GroupCollection::GROUP_TYPE_TAG, GroupCollection::GROUP_TYPE_TAG],
            [GroupCollection::GROUP_TYPE_TAG . '', null], //no strings
            [null, null],
            ['blah', null]
        ];
    }

    /**
     * @dataProvider provide_Group_Type_Assignment
     */
    public function test_Group_Type_Assignment($input, $expected)
    {
        $instance = new GroupCollection;
        $instance->forGroupType($input);

        $this->assertSame($instance->getGroupType(), $expected);
    }

    public function provide_Parent_Type_Assignment()
    {
        return [
            [GroupCollection::PARENT_TYPE_LOCATION, GroupCollection::PARENT_TYPE_LOCATION],
            [GroupCollection::PARENT_TYPE_LOCATION . '', null], //no strings
            [null, null],
            ['blah', null]
        ];
    }

    /**
     * @dataProvider provide_Parent_Type_Assignment
     */
    public function test_Parent_Type_Assignment($input, $expected)
    {
        $instance = new GroupCollection;
        $instance->forParentType($input);

        $this->assertSame($instance->getParentType(), $expected);
    }

    public function provid_Numeric_Assignment()
    {
        return [
            ['5', 5],
            [null, null],
            [5, 5],
            ['ipsum', null]
        ];
    }

    /**
     * @dataProvider provid_Numeric_Assignment
     */
    public function test_Parent_Id_Assignment($input, $expected)
    {
        $instance = new GroupCollection;
        $instance->forParentId($input);

        $this->assertSame($instance->getParentId(), $expected);
    }

    public function test_Parent_Assignment()
    {
        $parent = $this->createMock(Media::class);

        $parent
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));

        $instance = new GroupCollection;
        $instance->forParent($parent);

        $this->assertSame($instance->getParentType(), GroupCollection::PARENT_TYPE_MEDIA);
    }

    public function test_Parent_Assignment_Invalid_Type()
    {
        $this->expectException(InvalidArgumentException::class);

        $parent = $this->createMock(Entity::class);

        $instance = new GroupCollection;
        $instance->forParent($parent);
    }
}
