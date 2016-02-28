<?php

namespace src\models;

use src\exceptions\StateNotFound;
use src\exceptions\UserMessageNotFoundInAvailableOptions;

class State
{
    /**
     * @var array
     */
    private $state;

    public function __construct(array $script, $stateID)
    {
        // todo: validate input
        
        if (
            !array_key_exists('elements', $script)
            || !array_key_exists($stateID, $script['elements'])
        ) {
            throw new StateNotFound();
        }

        $this->state = $script['elements'][$stateID];
    }

    public function getBotMessage()
    {
        return $this->state['bot'];
    }

    public function getResponseOptions()
    {
        $user = $this->state['user'];
        $options = array_map(
            function ($item) {
                return [$item['text']];
            },
            $user
        );
        
        return array_values($options);
    }

    public function getFollowup()
    {
        if (!array_key_exists('followup', $this->state)) {
            return null;
        }

        return $this->state['followup'];
    }

    public function getFollowupByMessage($message)
    {
        $user = $this->state['user'];
        
        foreach ($user as $options) {
            if ($options['text'] == $message) {
                return $options['followup'];
            }
        }

        throw new UserMessageNotFoundInAvailableOptions(); 
    }
}