<?php

namespace CommunityVoices\App\Website\Controller;

use CommunityVoices\Model\Service;
use CommunityVoices\App\Website\Component;
use CommunityVoices\App\Api;

class Slide
{
    protected $recognitionAdapter;
    protected $slideAPIController;
    protected $secureContainer;

    public function __construct(
        Component\RecognitionAdapter $recognitionAdapter,
        Api\Controller\Slide $slideAPIController,
        Api\Component\SecureContainer $secureContainer
    ) {
        $this->recognitionAdapter = $recognitionAdapter;
        $this->slideAPIController = $slideAPIController;
        $this->secureContainer = $secureContainer;
    }

    public function getSlide($request)
    {
        $apiController = $this->secureContainer->contain($this->slideAPIController);

        $apiController->getSlide($request);
    }
}