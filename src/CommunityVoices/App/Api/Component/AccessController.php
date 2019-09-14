<?php

namespace CommunityVoices\App\Api\Component;

use CommunityVoices\Model\Component\StateObserver;

class AccessController
{
    private $identifier;
    private $logger;
    private $stateObserver;

    public function __construct(
        Contract\CanIdentify $identifier,
        \Psr\Log\LoggerInterface $logger,
        StateObserver $stateObserver
    ) {
        $this->identifier = $identifier;
        $this->logger = $logger;
        $this->stateObserver = $stateObserver;
    }

    // Maybe worth just storing in the class itself.
    protected function getUser()
    {
        return $this->identifier->identify();
    }

    // Reruns the access control function and throws an exception appropriately.
    public function redoAccessControl()
    {
        // Gets the name of the calling function, as that will determine which
        // function inside of this class is run.
        $function = debug_backtrace()[1]['function'];

        if (!method_exists($this, $function) || !call_user_func_array([$this, $function], [[]])) {
            $this->accessDenied();
        }
    }

    public function accessDenied()
    {
        $this->logger->error('AccessController AccessDenied Exception', ['message' => 'Access denied']);
        throw new Exception\AccessDenied($this->getUser());
    }
}
