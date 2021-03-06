<?php

namespace CommunityVoices\App\Website\Finder;

use \Exception;

class ImageFinderController
{
    private $responder;
    private $matcher;
    private $fileManager;


    const INPUT_ERR_BAD_DATA = "Bad form data submitted.";
    const INPUT_ERR_NO_FILE = "No file uploaded.";

    public function __construct(
        ImageFinderResponder $responder,
        ImageMatcher $matcher,
        FileManager $fileManager
    ) {
        $this->responder = $responder;
        $this->matcher = $matcher;
        $this->fileManager = $fileManager;
    }

    public function postMatchInquiry($params = [])
    {
        /**
         * Process input
         */
        $isSubmitted = $params['submit'];
        $uploadedImage = $params['image'];

        if (!$isSubmitted) {
            throw new Exception(self::INPUT_ERR_BAD_DATA);
        }

        if (!$uploadedImage || empty($uploadedImage['name'])) {
            throw new Exception(self::INPUT_ERR_NO_FILE);
        }

        // Upload temporary file
        $tempFilepath = $this->fileManager->upload($uploadedImage);

        // Find matches and delete temporary file
        $matches = $this->matcher->findCloseMatches($tempFilepath);
        $this->fileManager->delete($tempFilepath);

        /**
         * View collection of matches
         */
        return $this->responder->matchesResponse($matches);
    }

    public function getInputForm($params = [])
    {
        return $this->responder->inputResponse();
    }

    public function getErrorNotice($params = [])
    {
        if (!array_key_exists('error', $params)) {
            $params['error'] = "General error encountered.";
        }

        return $this->responder->errorResponse($params['error']);
    }
}
