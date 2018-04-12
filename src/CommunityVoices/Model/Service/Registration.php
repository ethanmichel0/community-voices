<?php

namespace CommunityVoices\Model\Service;

/**
 * @overview Responsible for resgistration-related services
 */

use Palladium;
use CommunityVoices\Model\Entity;
use CommunityVoices\Model\Component;
use CommunityVoices\Model\Mapper;

class Registration
{
    private $pdRegistration;

    private $mapperFactory;

    private $stateObserver;

    public function __construct(
        Palladium\Service\Registration $pdRegistration,
        Component\MapperFactory $mapperFactory,
        Component\StateObserver $stateObserver
    ) {
        $this->pdRegistration = $pdRegistration;
        $this->mapperFactory = $mapperFactory;
        $this->stateObserver = $stateObserver;
    }

    /**
     * Registers a user in the database
     * @param  string $email
     * @param  string $password
     * @param  string $confirmPassword
     * @param  string $firstName
     * @param  string $lastName
     * @return boolean True indicates success
     */
    public function createUser($email, $password, $confirmPassword, $firstName, $lastName)
    {
        /**
         * Create user entity and set attributes
         */
        $user = new Entity\User;

        $user->setEmail($email);
        $user->setPassword($password);
        $user->setConfirmPassword($confirmPassword);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRole($user::ROLE_UNVERIFIED);


        /**
         * Create error observer, set the subject, and pass it to user validator
         */
        $this->stateObserver->setSubject('registration');

        $isValid = $user->validateForRegistration($this->stateObserver);

        $clientState = $this->mapperFactory->createClientStateMapper(Mapper\ClientState::class);

        /**
         * Stop the registration process and save the errors to application state
         * if email is invalid. No point in continuing the validation process in
         * making sure no user has this email if the email is invalid anyway
         */
        if (!$isValid && $this->stateObserver->hasEntry('email', $user::ERR_EMAIL_INVALID)) {
            $clientState->save($this->stateObserver);
            return false;
        }

        $userMapper = $this->mapperFactory->createDataMapper(Mapper\User::class);

        if ($userMapper->existingUserWithEmail($user)) {
            $this->stateObserver->addEntry('email', $user::ERR_EMAIL_EXISTS);
        }

        /**
         * If there are any errors at this point, save the error state and stop
         * the registration process
         */
        if ($this->stateObserver->hasEntries()) {
            $clientState->save($this->stateObserver);
            return false;
        }

        /**
         * Register this user; save with the user mapper and with Palladium
         */
        $userMapper->save($user);

        //`createEmailIdentity()` shouldn't throw IdentityConflict exception
        $pdIdentity = $this->pdRegistration->createEmailIdentity($email, $password);
        $this->pdRegistration->bindAccountToIdentity($user->getId(), $pdIdentity);
        $this->pdRegistration->verifyEmailIdentity($pdIdentity);

        return true;
    }
}
