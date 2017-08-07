<?php

namespace CommunityVoices\Model\Entity;

use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * @covers CommunityVoices\Model\Entity\Slide
 */
class SlideTest extends TestCase
{
    /**
     * @TODO content categories should be DO
     */

     public function test_Image_Id_Assignment()
     {
         $instance = new Slide;
         $instance->setId(5);

         $this->assertSame($instance->getId(), 5);
     }

    public function test_Content_Category_Assignment()
    {
        $contentCategory = $this->createMock(ContentCategory::class);

        $instance = new Slide;
        $instance->setContentCategory($contentCategory);

        $this->assertSame($instance->getContentCategory(), $contentCategory);
    }

    public function test_Content_Category_Invalid_Assignment()
    {
        $this->expectException(TypeError::class);

        $contentCategory = [];

        $instance = new Slide;
        $instance->setContentCategory($contentCategory);
    }

    public function test_Org_Category_Collection_Assignment()
    {
        $orgCatCollection = $this->createMock(GroupCollection::class);

        $instance = new Slide;
        $instance->setOrganizationCategoryCollection($orgCatCollection);

        $this->assertSame($instance->getOrganizationCategoryCollection(), $orgCatCollection);
    }

    public function test_Org_Category_Collection_Invalid_Assignment()
    {
        $this->expectException(TypeError::class);

        $orgCatCollection = [];

        $instance = new Slide;
        $instance->setOrganizationCategoryCollection($orgCatCollection);
    }

    public function test_Image_Assignment()
    {
        $image = $this->createMock(Image::class);

        $instance = new Slide;
        $instance->setImage($image);

        $this->assertSame($instance->getImage(), $image);
    }

    public function test_Quote_Assignment()
    {
        $quote = $this->createMock(Quote::class);

        $instance = new Slide;
        $instance->setQuote($quote);

        $this->assertSame($instance->getQuote(), $quote);
    }

    public function test_Probability_Assignment()
    {
        $instance = new Slide;
        $instance->setProbability(.3);

        $this->assertSame($instance->getProbability(), .3);
    }

    public function test_Decay_Percent_Assignment()
    {
        $instance = new Slide;
        $instance->setDecayPercent(.3);

        $this->assertSame($instance->getDecayPercent(), .3);
    }

    public function test_Decay_Start_Assignment()
    {
        $time = strtotime('+5 days');

        $instance = new Slide;
        $instance->setDecayStart($time);

        $this->assertSame($instance->getDecayStart(), $time);
    }

    public function test_Decay_End_Assignment()
    {
        $time = strtotime('+5 days');

        $instance = new Slide;
        $instance->setDecayEnd($time);

        $this->assertSame($instance->getDecayEnd(), $time);
    }

    // @TODO test validation
}
