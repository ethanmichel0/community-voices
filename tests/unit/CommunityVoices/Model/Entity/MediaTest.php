<?php

namespace CommunityVoices\Model\Entity;

use PHPUnit\Framework\TestCase;

/**
 * @covers CommunityVoices\Model\Entity\Media
 */
class MediaTest extends TestCase
{
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
    public function test_Id_Assignment($input, $expected)
    {
        $instance = new Media;
        $instance->setId($input);

        $this->assertSame($instance->getId(), $expected);
    }

    public function test_User_Assignment()
    {
        $user = new User;
        $user->setId(5);

        $instance = new Media;
        $instance->setAddedBy($user);

        $this->assertSame($instance->getAddedBy(), $user);
    }

    public function test_Date_Created_Assignment()
    {
        $now = time();

        $instance = new Media;
        $instance->setDateCreated($now);

        $this->assertSame($instance->getDateCreated(), $now);
    }

    public function provide_Type_Assignment()
    {
        return [
            [Media::TYPE_SLIDE, Media::TYPE_SLIDE],
            [1, Media::TYPE_SLIDE],
            ['1', Media::TYPE_SLIDE],
            ['foo', null],
            [null, null]
        ];
    }

    /**
     * @dataProvider provide_Type_Assignment
     */
    public function test_Type_Assignment($input, $expected)
    {
        $instance = new Media;
        $instance->setType($input);

        $this->assertSame($instance->getType(), $expected);
    }

    public function provide_Status_Assignment()
    {
        return [
            [Media::TYPE_IMAGE, Media::TYPE_IMAGE],
            [1, Media::TYPE_IMAGE],
            ['1', Media::TYPE_IMAGE],
            ['foo', null],
            [null, null]
        ];
    }

    /**
     * @dataProvider provide_Status_Assignment
     */
    public function test_Status_Assignment()
    {
        $instance = new Media;
        $instance->setStatus($instance::STATUS_APPROVED);

        $this->assertSame($instance->getStatus(), $instance::STATUS_APPROVED);
    }
}
