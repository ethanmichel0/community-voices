<?php

namespace CommunityVoices\App\Website\View;

use \SimpleXMLElement;
use \DOMDocument;
use \XSLTProcessor;

use CommunityVoices\App\Api;
use CommunityVoices\App\Website\Component;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing\Generator\UrlGenerator;

class Quote extends Component\View
{
    protected $recognitionAdapter;
    protected $quoteAPIView;
    protected $secureContainer;
    protected $transcriber;

    public function __construct(
        Component\RecognitionAdapter $recognitionAdapter,
        Component\MapperFactory $mapperFactory,
        Component\Transcriber $transcriber,
        Api\Component\SecureContainer $secureContainer,
        Api\View\Quote $quoteAPIView
    ) {
        $this->recognitionAdapter = $recognitionAdapter;
        $this->mapperFactory = $mapperFactory;
        $this->transcriber = $transcriber;
        $this->secureContainer = $secureContainer;
        $this->quoteAPIView = $quoteAPIView;
    }

    public function getQuote($routes, $context)
    {
        /**
         * Gather identity information
         */
        $identity = $this->recognitionAdapter->identify();

        $identityXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($identity->toArray())
        );

        /**
         * Gather quote information
         */
        $quoteAPIView = $this->secureContainer->contain($this->quoteAPIView);

        $quoteXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml(json_decode(
                $quoteAPIView->getQuote()->getContent()
            ))
        );

        /**
         * Quote XML Package
         */
        $quotePackageElement = new Helper\SimpleXMLElementExtension('<package/>');

        $packagedQuote = $quotePackageElement->addChild('domain');
        $packagedQuote->adopt($quoteXMLElement);

        $packagedIdentity = $quotePackageElement->addChild('identity');
        $packagedIdentity->adopt($identityXMLElement);

        /**
         * Generate Quote module
         */
        // var_dump($quotePackageElement->domain->quote->tagCollection->groupCollection ->group->label);die;
        $quoteModule = new Component\Presenter('Module/Quote');
        $quoteModuleXML = $quoteModule->generate($quotePackageElement);

        /**
         * Get base URL
         */
        $urlGenerator = new UrlGenerator($routes, $context);
        $baseUrl = $urlGenerator->generate('root');

        /**
         * Prepare template
         */
        $domainXMLElement = new Helper\SimpleXMLElementExtension('<domain/>');

        $domainXMLElement->addChild('main-pane', $quoteModuleXML);
        $domainXMLElement->addChild('baseUrl', $baseUrl);
        $domainXMLElement->addChild(
            'title',
            "Community Voices: Quote ".
            $quoteXMLElement->id
        );
        $domainXMLElement->addChild('navbarSection', "quote");

        $domainIdentity = $domainXMLElement->addChild('identity');
        $domainIdentity->adopt($identityXMLElement);

        $presentation = new Component\Presenter('SinglePane');

        $response = new HttpFoundation\Response($presentation->generate($domainXMLElement));

        $this->finalize($response);
        return $response;
    }

    public function getAllQuote($routes, $context)
    {
        parse_str($_SERVER['QUERY_STRING'], $qs);

        /**
         * Gather identity information
         */
        $identity = $this->recognitionAdapter->identify();

        $identityXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($identity->toArray())
        );

        /**
         * Gather quote information
         */
        $quoteAPIView = $this->secureContainer->contain($this->quoteAPIView);
        $json = json_decode($quoteAPIView->getAllQuote()->getContent());

        $obj = new \stdClass();
        $obj->quoteCollection = $json->quoteCollection;
        $count = $obj->quoteCollection->count;
        $limit = $obj->quoteCollection->limit;
        $page = $obj->quoteCollection->page;
        unset($obj->quoteCollection->count);
        unset($obj->quoteCollection->limit);
        unset($obj->quoteCollection->page);
        $obj->quoteCollection = array_values((array) $obj->quoteCollection);

        $quoteXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($obj)
        );

        $tags = $json->tags;
        $tagXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($tags)
        );

        $pagination = new \stdClass();
        $pagination->div = $this->paginationHTML($qs, $count, $limit, $page);
        $paginationXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($pagination)
        );

        $attributions = $json->quoteCollectionAttributions;
        $attributionXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($attributions)
        );

        /**
         * Quote XML Package
         */
        $quotePackageElement = new Helper\SimpleXMLElementExtension('<package/>');

        $packagedQuote = $quotePackageElement->addChild('domain');
        $packagedQuote->adopt($quoteXMLElement);
        $packagedQuote->adopt($tagXMLElement);
        $packagedQuote->adopt($attributionXMLElement);
        $packagedQuote->adopt($paginationXMLElement);

        foreach ($qs as $key => $value) {
            if ($key === 'search') {
                $packagedQuote->addChild($key, $value);
            } else {
                $packagedQuote->addChild($key, (is_array($value)) ? ','.implode(',', $value).',' : ','.$value.',');
            }
        }

        $packagedIdentity = $quotePackageElement->addChild('identity');
        $packagedIdentity->adopt($identityXMLElement);

        /**
         * Generate Quote module
         */
        $quoteModule = new Component\Presenter('Module/QuoteCollection');
        $quoteModuleXML = $quoteModule->generate($quotePackageElement);

        /**
         * Get base URL
         */
        $urlGenerator = new UrlGenerator($routes, $context);
        $baseUrl = $urlGenerator->generate('root');

        /**
         * Prepare template
         */
        $domainXMLElement = new Helper\SimpleXMLElementExtension('<domain/>');

        $domainXMLElement->addChild('main-pane', $quoteModuleXML);
        $domainXMLElement->addChild('baseUrl', $baseUrl);
        $domainXMLElement->addChild('title', "Community Voices: All Quotes");

        $domainIdentity = $domainXMLElement->addChild('identity');
        $domainIdentity->adopt($identityXMLElement);

        $presentation = new Component\Presenter('SinglePane');

        $response = new HttpFoundation\Response($presentation->generate($domainXMLElement));

        $this->finalize($response);
        return $response;
    }

    public function getQuoteUpload($routes, $context)
    {

        $identity = $this->recognitionAdapter->identify();

        $identityXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($identity->toArray())
        );

        $quoteAPIView = $this->secureContainer->contain($this->quoteAPIView);

        $quoteXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml(json_decode(
                $quoteAPIView->getQuoteUpload()->getContent()
            ))
        );

        $quotePackageElement = new Helper\SimpleXMLElementExtension('<package/>');
        $packagedQuote = $quotePackageElement->addChild('domain');
        $packagedQuote->adopt($quoteXMLElement);
        $packagedIdentity = $quotePackageElement->addChild('identity');
        $packagedIdentity->adopt($identityXMLElement);
        $quoteModule = new Component\Presenter('Module/Form/QuoteUpload');
        $quoteModuleXML = $quoteModule->generate($quotePackageElement);
        /**
         * Get base URL
         */
        $urlGenerator = new UrlGenerator($routes, $context);
        $baseUrl = $urlGenerator->generate('root');
        /**
         * Prepare template
         */
        $domainXMLElement = new Helper\SimpleXMLElementExtension('<domain/>');
        $domainXMLElement->addChild('main-pane', $quoteModuleXML);
        $domainXMLElement->addChild('baseUrl', $baseUrl);
        $domainXMLElement->addChild('title', "Community Voices: Quote Upload");
        $domainIdentity = $domainXMLElement->addChild('identity');
        $domainIdentity->adopt($identityXMLElement);
        $presentation = new Component\Presenter('SinglePane');
        $response = new HttpFoundation\Response($presentation->generate($domainXMLElement));
        $this->finalize($response);
        return $response;
    }

    public function postQuoteUpload($routes, $context)
    {
        $identity = $this->recognitionAdapter->identify();
        $identityXMLElement = new SimpleXMLElement(
          $this->transcriber->toXml($identity->toArray())
        );

        /**
         * Get base URL
         */
        $urlGenerator = new UrlGenerator($routes, $context);
        $baseUrl = $urlGenerator->generate('root');

        $domainXMLElement = new Helper\SimpleXMLElementExtension('<domain/>');

        $domainXMLElement->addChild('main-pane', '<p>Success.</p>');
        $domainXMLElement->addChild('baseUrl', $baseUrl);

        $domainXMLElement->addChild(
          'title',
          "Community Voices"
        );
        $domainXMLElement->addChild('navbarSection', "quote");

        /**
         * Prepare template
         */
        $domainIdentity = $domainXMLElement->addChild('identity');
        $domainIdentity->adopt($identityXMLElement);

        $presentation = new Component\Presenter('SinglePane');

        $response = new HttpFoundation\Response($presentation->generate($domainXMLElement));

        $this->finalize($response);
        return $response;
    }

    public function getQuoteUpdate($routes, $context)
    {
        $paramXML = new Helper\SimpleXMLElementExtension('<form/>');

        /**
         * Gather quote information
         */
        $quoteAPIView = $this->secureContainer->contain($this->quoteAPIView);

        $quoteXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml(json_decode(
                $quoteAPIView->getQuote()->getContent()
            ))
        );

        $packagedQuote = $paramXML->addChild('domain');
        $packagedQuote->adopt($quoteXMLElement);

        $formModule = new Component\Presenter('Module/Form/QuoteUpdate');
        $formModuleXML = $formModule->generate($paramXML);

        $identity = $this->recognitionAdapter->identify();

        $identityXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($identity->toArray())
        );

        /**
         * Get base URL
         */
        $urlGenerator = new UrlGenerator($routes, $context);
        $baseUrl = $urlGenerator->generate('root');

        //

        $domainXMLElement = new Helper\SimpleXMLElementExtension('<domain/>');

        $domainXMLElement->addChild('main-pane', $formModuleXML);
        $domainXMLElement->addChild('baseUrl', $baseUrl);
        $domainXMLElement->addChild(
            'title',
            "Community Voices: Quote Update"
        );
        $domainXMLElement->addChild('navbarSection', "quote");

        $domainIdentity = $domainXMLElement->addChild('identity');
        $domainIdentity->adopt($identityXMLElement);

        // var_dump($domainIdentity);

        $presentation = new Component\Presenter('SinglePane');

        $response = new HttpFoundation\Response($presentation->generate($domainXMLElement));

        $this->finalize($response);
        return $response;
    }

    public function postQuoteUpdate($routes, $context)
    {
        $identity = $this->recognitionAdapter->identify();
        $identityXMLElement = new SimpleXMLElement(
          $this->transcriber->toXml($identity->toArray())
        );

        /**
         * Get base URL
         */
        $urlGenerator = new UrlGenerator($routes, $context);
        $baseUrl = $urlGenerator->generate('root');

        $domainXMLElement = new Helper\SimpleXMLElementExtension('<domain/>');

        $domainXMLElement->addChild('main-pane', '<p>Success.</p>');
        $domainXMLElement->addChild('baseUrl', $baseUrl);

        $domainXMLElement->addChild(
          'title',
          "Community Voices"
        );
        $domainXMLElement->addChild('navbarSection', "quote");

        /**
         * Prepare template
         */
        $domainIdentity = $domainXMLElement->addChild('identity');
        $domainIdentity->adopt($identityXMLElement);

        $presentation = new Component\Presenter('SinglePane');

        $response = new HttpFoundation\Response($presentation->generate($domainXMLElement));

        $this->finalize($response);
        return $response;
    }
}
