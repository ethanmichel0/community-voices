<?php

namespace CommunityVoices\App\Website\View;

use \SimpleXMLElement;
use \DOMDocument;
use \XSLTProcessor;

use CommunityVoices\App\Api;
use CommunityVoices\App\Website\Component;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing\Generator\UrlGenerator;

class Slide extends Component\View
{
    protected $recognitionAdapter;
    protected $slideAPIView;
    protected $secureContainer;
    protected $transcriber;

    public function __construct(
        Component\RecognitionAdapter $recognitionAdapter,
        Component\MapperFactory $mapperFactory,
        Component\Transcriber $transcriber,
        Api\Component\SecureContainer $secureContainer,
        Api\View\Slide $slideAPIView
    ) {
        $this->recognitionAdapter = $recognitionAdapter;
        $this->mapperFactory = $mapperFactory;
        $this->transcriber = $transcriber;
        $this->secureContainer = $secureContainer;
        $this->slideAPIView = $slideAPIView;
    }

    public function getSlide($routes, $context)
    {
        /**
         * Gather identity information
         */
        $identity = $this->recognitionAdapter->identify();

        $identityXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($identity->toArray())
        );

        /**
         * Gather slide information
         */
        $slideAPIView = $this->secureContainer->contain($this->slideAPIView);

        $slideXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml(json_decode(
                $slideAPIView->getSlide()->getContent()
            ))
        );

        /**
         * Slide XML Package
         */
        $slidePackageElement = new Helper\SimpleXMLElementExtension('<package/>');

        $packagedSlide = $slidePackageElement->addChild('domain');
        $packagedSlide->adopt($slideXMLElement);

        $packagedIdentity = $slidePackageElement->addChild('identity');
        $packagedIdentity->adopt($identityXMLElement);

        /**
         * Generate slide module
         */
        $slideModule = new Component\Presenter('Module/Slide');
        $slideModuleXML = $slideModule->generate($slidePackageElement);

        /**
         * Get base URL
         */
        $urlGenerator = new UrlGenerator($routes, $context);
        $baseUrl = $urlGenerator->generate('root');

        /**
         * Prepare template
         */
        $domainXMLElement = new Helper\SimpleXMLElementExtension('<domain/>');

        $domainXMLElement->addChild('main-pane', $slideModuleXML);
        $domainXMLElement->addChild('baseUrl', $baseUrl);
        $domainXMLElement->addChild(
            'title',
            "Community Voices: Slide ".
            $slideXMLElement->id
        );

        $domainIdentity = $domainXMLElement->addChild('identity');
        $domainIdentity->adopt($identityXMLElement);

        $presentation = new Component\Presenter('SinglePane');

        $response = new HttpFoundation\Response($presentation->generate($domainXMLElement));

        $this->finalize($response);
        return $response;
    }
}