<?php

namespace CommunityVoices\Model\Entity;

class Group
{
    const TYPE_TAG = 1;
    const TYPE_ORG_CAT = 2;
    const TYPE_CONT_CAT = 3;

    protected $allowableType = [
        self::TYPE_TAG,
        self::TYPE_ORG_CAT,
        self::TYPE_CONT_CAT
    ];

    private $id;

    private $label;

    protected $type;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $input = (int) $id;

        if ($input > 0) {
            $this->id = $input;
        }
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        if (in_array($type, $this->allowableType)) {
            $this->type = (int) $type;
        }
    }
}