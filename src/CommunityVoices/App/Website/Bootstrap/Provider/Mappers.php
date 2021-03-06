<?php

namespace CommunityVoices\App\Website\Bootstrap\Provider;

use CommunityVoices\Model;
use CommunityVoices\App\Api;
use CommunityVoices\App\Website;
use CommunityVoices\App\Website\Component\Provider;
use \Palladium;

/**
 * @overview Mappers provider
 */

class Mappers extends Provider
{
    protected $dbHandler;
    protected $request;

    public function __construct($injector, \PDO $dbHandler, $request)
    {
        parent::__construct($injector);

        $this->dbHandler = $dbHandler;
        $this->request = $request;
    }

    public function init()
    {
        $APIMapperFactory = new Api\Component\MapperFactory($this->request);
        $websiteMapperFactory = new Website\Component\MapperFactory($this->request);
        $modelMapperFactory = new Model\Component\MapperFactory($this->dbHandler);

        /**
         * @config?
         */
        $pdMapperFactory = new Palladium\Component\MapperFactory($this->dbHandler, '`community-voices_identities`');

        $this->injector->share($APIMapperFactory);
        $this->injector->share($websiteMapperFactory);
        $this->injector->share($modelMapperFactory);
        $this->injector->share($pdMapperFactory);

        $this->injector->alias('Palladium\Contract\CanCreateMapper', 'Palladium\Component\MapperFactory');
    }
}
