<?php

namespace CommunityVoices\App\Website\View;

use \SimpleXMLElement;
use \DOMDocument;
use \XSLTProcessor;

use CommunityVoices\App\Api;
use CommunityVoices\App\Website\Component;
use CommunityVoices\Model\Service;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing\Generator\UrlGenerator;

class Image extends Component\View
{
    protected $imageAPIView;
    protected $imageLookup;
    protected $tagLookup;
    protected $urlGenerator;

    public function __construct(
        Component\MapperFactory $mapperFactory,
        Component\Transcriber $transcriber,
        Api\View\Identification $identificationAPIView,
        Api\View\Image $imageAPIView,
        Service\ImageLookup $imageLookup,
        Service\TagLookup $tagLookup,
        UrlGenerator $urlGenerator
    ) {
        parent::__construct($mapperFactory, $transcriber, $identificationAPIView);

        $this->imageAPIView = $imageAPIView;
        $this->imageLookup = $imageLookup;
        $this->tagLookup = $tagLookup;
        $this->urlGenerator = $urlGenerator;
    }

    public function sendImage($request)
    {
        // wut
    }

    public function getImage($request)
    {
        /**
         * Gather image information
         */
        $json = json_decode($this->imageAPIView->getImage()->getContent());
        $imageXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($json)
        );

        /**
         * image XML Package
         */
        $imagePackageElement = new Helper\SimpleXMLElementExtension('<package/>');

        $packagedimage = $imagePackageElement->addChild('domain');
        $packagedimage->adopt($imageXMLElement);
        $packagedimage->adopt(new SimpleXMLElement(
            $this->transcriber->toXml(['slideId' => $this->imageLookup->relatedSlide($json->image->id)])
        ));
        $packagedimage->adopt(new SimpleXMLElement(
            $this->transcriber->toXml(['prevId' => $this->imageLookup->prevImage($json->image->id)])
        ));
        $packagedimage->adopt(new SimpleXMLElement(
            $this->transcriber->toXml(['nextId' => $this->imageLookup->nextImage($json->image->id)])
        ));

        $packagedIdentity = $imagePackageElement->addChild('identity');
        $packagedIdentity->adopt($this->identityXMLElement());

        /**
         * Generate image module
         */
        $imageModule = new Component\Presenter('Module/Image');
        $imageModuleXML = $imageModule->generate($imagePackageElement);

        /**
         * Get base URL
         */
        //$urlGenerator = new UrlGenerator($routes, $context);
        //$baseUrl = $urlGenerator->generate('root');

        /**
         * Prepare template
         */
        $domainXMLElement = new Helper\SimpleXMLElementExtension('<domain/>');

        $domainXMLElement->addChild('main-pane', $imageModuleXML);
        //$domainXMLElement->addChild('baseUrl', $baseUrl);
        $domainXMLElement->addChild(
            'title',
            "Community Voices: Image ".
            $imageXMLElement->id
        );


        $domainIdentity = $domainXMLElement->addChild('identity');
        $domainIdentity->adopt($this->identityXMLElement());

        $presentation = new Component\Presenter('SinglePane');

        $response = new HttpFoundation\Response($presentation->generate($domainXMLElement));

        $this->finalize($response);
        return $response;
    }

    public function getAllImage($request)
    {
        parse_str($_SERVER['QUERY_STRING'], $qs);

        /**
         * Gather image information
         */
        $json = json_decode($this->imageAPIView->getAllImage()->getContent());
        $obj = new \stdClass();
        $obj->imageCollection = $json->imageCollection;
        $count = $obj->imageCollection->count;
        $limit = $obj->imageCollection->limit;
        $page = $obj->imageCollection->page;
        unset($obj->imageCollection->count); // TODO: fix!
        unset($obj->imageCollection->limit);
        unset($obj->imageCollection->page);
        $obj->imageCollection = array_values((array) $obj->imageCollection);
        // add csv of tags so checkboxes can be checked with xslt
        foreach ($obj->imageCollection as $item) {
            $selectedTags = [];
            foreach ($item->image->tagCollection->groupCollection as $group) {
                $selectedTags[] = $group->group->id;
            }
            $item->image->selectedTagString = ',' . implode(',', $selectedTags) . ',';
            $item->image->relatedSlide = $this->imageLookup->relatedSlide($item->image->id);
        }

        $imageXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($obj)
        );

        // Get all photographers for menu -- should this be done a different way?
        $photographers = $json->imageCollectionPhotographers;
        $photographerXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($photographers)
        );

        $orgs = $json->imageCollectionOrgs;
        $orgXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($orgs)
        );

        $tags = $json->tags;
        usort($tags->tagCollection, function ($a, $b) {
            $a = $a->tag->label;
            $b = $b->tag->label;
            return strcmp($a, $b);
        });
        $tagXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($tags)
        );

        // TODO fix
        $pagination = new \stdClass();
        $pagination->div = $this->paginationHTML($qs, $count, $limit, $page);
        $paginationXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($pagination)
        );

        /**
         * image XML Package
         */
        $imagePackageElement = new Helper\SimpleXMLElementExtension('<package/>');

        $packagedImage = $imagePackageElement->addChild('domain');
        $packagedImage->adopt($imageXMLElement);
        $packagedImage->adopt($photographerXMLElement);
        $packagedImage->adopt($orgXMLElement);
        $packagedImage->adopt($paginationXMLElement);
        $packagedImage->adopt($tagXMLElement);

        foreach ($qs as $key => $value) {
            if ($key === 'search' || $key === 'order' || $key === 'unused') {
                $packagedImage->addChild($key, $value);
            } else {
                $packagedImage->addChild($key, (is_array($value)) ? ','.implode(',', $value).',' : ','.$value.',');
            }
        }

        $packagedIdentity = $imagePackageElement->addChild('identity');
        $packagedIdentity->adopt($this->identityXMLElement());

        /**
         * Generate image module
         */
        $imageModule = new Component\Presenter('Module/ImageCollection');
        $imageModuleXML = $imageModule->generate($imagePackageElement);

        /**
         * Get base URL
         */
        //$urlGenerator = new UrlGenerator($routes, $context);
        //$baseUrl = $urlGenerator->generate('root');

        /**
         * Prepare template
         */
        $domainXMLElement = new Helper\SimpleXMLElementExtension('<domain/>');

        $domainXMLElement->addChild('main-pane', $imageModuleXML);
        //$domainXMLElement->addChild('baseUrl', $baseUrl);
        $domainXMLElement->addChild('title', "Community Voices: All Images");
        $domainXMLElement->addChild('extraJS', "https://cdn.jsdelivr.net/npm/exif-js image-collection");
        $domainXMLElement->addChild('extraCSS', "image-collection");
        $domainXMLElement->addChild('metaDescription', "Searchable database of photos used for Community Voices communication technology to promote environmental, social and economic sustainability in diverse communities.");
        $domainIdentity = $domainXMLElement->addChild('identity');
        $domainIdentity->adopt($this->identityXMLElement());

        $presentation = new Component\Presenter('SinglePane');

        $response = new HttpFoundation\Response($presentation->generate($domainXMLElement));

        $this->finalize($response);
        return $response;
    }

    public function getImageUpload($request)
    {
        $imageXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml(json_decode(
                $this->imageAPIView->getImageUpload()->getContent()
            ))
        );

        $imagePackageElement = new Helper\SimpleXMLElementExtension('<package/>');
        $packagedImage = $imagePackageElement->addChild('domain');
        $packagedImage->adopt($imageXMLElement);
        $packagedIdentity = $imagePackageElement->addChild('identity');
        $packagedIdentity->adopt($this->identityXMLElement());
        $imageModule = new Component\Presenter('Module/Form/ImageUpload');
        $imageModuleXML = $imageModule->generate($imagePackageElement);
        /**
         * Get base URL
         */
        //$urlGenerator = new UrlGenerator($routes, $context);
        //$baseUrl = $urlGenerator->generate('root');
        /**
         * Prepare template
         */
        $domainXMLElement = new Helper\SimpleXMLElementExtension('<domain/>');
        $domainXMLElement->addChild('main-pane', $imageModuleXML);
        //$domainXMLElement->addChild('baseUrl', $baseUrl);
        $domainXMLElement->addChild('title', "Community Voices: Image Upload");
        $domainXMLElement->addChild('extraJS', "https://cdn.jsdelivr.net/npm/exif-js image-upload");
        $domainIdentity = $domainXMLElement->addChild('identity');
        $domainIdentity->adopt($this->identityXMLElement());
        $presentation = new Component\Presenter('SinglePane');
        $response = new HttpFoundation\Response($presentation->generate($domainXMLElement));
        $this->finalize($response);
        return $response;
    }

    public function postImageUpload($request)
    {
        $response = new HttpFoundation\RedirectResponse(
            $this->urlGenerator->generate('getAllImage')
        );

        $this->finalize($response);
        return $response;
    }

    public function getImageUpdate($request)
    {
        $paramXML = new Helper\SimpleXMLElementExtension('<form/>');

        /**
         * Gather image information
         */
        $image = json_decode($this->imageAPIView->getImage()->getContent());
        $imageXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($image)
        );

        $tags = $this->tagLookup->findAll(true);
        $tagXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml($tags->getEntry('tag')[0]->toArray())
        );

        $selectedTagString = ',';
        foreach ($image->image->tagCollection->groupCollection as $group) {
            $selectedTagString .= "{$group->group->id},";
        }
        $selectedTagXMLElement = new SimpleXMLElement(
            $this->transcriber->toXml(['selectedTags' => [$selectedTagString]])
        );

        $packagedImage = $paramXML->addChild('domain');
        $packagedImage->adopt($imageXMLElement);
        $packagedImage->adopt($tagXMLElement);
        $packagedImage->adopt($selectedTagXMLElement);

        $formModule = new Component\Presenter('Module/Form/ImageUpdate');
        $formModuleXML = $formModule->generate($paramXML);

        /**
         * Get base URL
         */
        //$urlGenerator = new UrlGenerator($routes, $context);
        //$baseUrl = $urlGenerator->generate('root');

        //

        $domainXMLElement = new Helper\SimpleXMLElementExtension('<domain/>');

        $domainXMLElement->addChild('main-pane', $formModuleXML);
        //$domainXMLElement->addChild('baseUrl', $baseUrl);
        $domainXMLElement->addChild('title', "Community Voices: Image Update");
        $domainXMLElement->addChild('extraJS', "image-update");


        $domainIdentity = $domainXMLElement->addChild('identity');
        $domainIdentity->adopt($this->identityXMLElement());

        $presentation = new Component\Presenter('SinglePane');

        $response = new HttpFoundation\Response($presentation->generate($domainXMLElement));

        $this->finalize($response);
        return $response;
    }

    public function postImageUpdate($request)
    {
        $response = new HttpFoundation\RedirectResponse(
            $request->headers->get('referer')
        );

        $this->finalize($response);
        return $response;
    }

    public function postImageUnpair($request)
    {
        exit; // nothing to show to user
    }
}
