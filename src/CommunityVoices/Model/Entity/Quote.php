<?php

namespace CommunityVoices\Model\Entity;

use CommunityVoices\Model\Contract\FlexibleObserver;

class Quote extends Media
{
    const ERR_ATTRIBUTION_REQUIRED = 'Quotes must have an attribution.';
    const ERR_SOURCE_LINK_INVALID = 'Source document link must be empty or a valid URL.';
    const ERR_PUBLIC_LINK_INVALID = 'Public document link must be empty or a valid URL.';
    const ERR_MISSING_CONTENT_CATEGORY = 'Must provide a potential content category.';

    private $text;

    private $attribution;
    private $subAttribution;
    private $dateRecorded;

    private $publicDocumentLink;
    private $sourceDocumentLink;

    public $type;

    public function __construct()
    {
        $this->type = self::TYPE_QUOTE;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = htmlspecialchars($text);
    }

    public function getAttribution()
    {
        return $this->attribution;
    }

    public function setAttribution($attribution)
    {
        $this->attribution = htmlspecialchars($attribution);
    }

    public function getSubAttribution()
    {
        return $this->subAttribution;
    }

    public function setSubAttribution($subAttribution)
    {
        $this->subAttribution = htmlspecialchars($subAttribution);
    }

    public function getDateRecorded()
    {
        return $this->dateRecorded;
    }

    public function setDateRecorded($dateRecorded)
    {
        $this->dateRecorded = strtotime($dateRecorded);
    }

    public function getPublicDocumentLink()
    {
        return $this->publicDocumentLink;
    }

    public function setPublicDocumentLink($publicDocumentLink)
    {
        $this->publicDocumentLink = $publicDocumentLink;
    }

    public function getSourceDocumentLink()
    {
        return $this->sourceDocumentLink;
    }

    public function setSourceDocumentLink($sourceDocumentLink)
    {
        $this->sourceDocumentLink = $sourceDocumentLink;
    }


    public function validateForUpload(FlexibleObserver $stateObserver, array $contentCategories)
    {
        $isValid = true;

        if (!$this->attribution || empty($this->attribution)) {
            $isValid = false;
            $stateObserver->addEntry('attribution', self::ERR_ATTRIBUTION_REQUIRED);
        }

        if ($this->sourceDocumentLink && !filter_var($this->sourceDocumentLink, FILTER_VALIDATE_URL)) {
            $isValid = false;
            $stateObserver->addEntry('sourceDocumentLink', self::ERR_SOURCE_LINK_INVALID);
        }

        if ($this->publicDocumentLink && !filter_var($this->publicDocumentLink, FILTER_VALIDATE_URL)) {
            $isValid = false;
            $stateObserver->addEntry('publicDocumentLink', self::ERR_PUBLIC_LINK_INVALID);
        }

        if (empty($contentCategories)) {
            $isValid = false;
            $stateObserver->addEntry('contentCategory', self::ERR_MISSING_CONTENT_CATEGORY);
        }

        return $isValid;
    }

    public function toArray()
    {
        return ['quote' => array_merge(parent::toArray()['media'], [
            'text' => $this->text,
            'attribution' => $this->attribution,
            'subAttribution' => $this->subAttribution,
            'dateRecorded' => date("Y-m-d H:i:s", $this->dateRecorded),
            'publicDocumentLink' => $this->publicDocumentLink,
            'sourceDocumentLink' => $this->sourceDocumentLink
        ])];
    }
}
