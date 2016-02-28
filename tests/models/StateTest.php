<?php

namespace tests\models;


use src\models\State;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldThrowStateNotFoundExceptionForGetBotMessage()
    {
        $this->setExpectedException('\src\exceptions\StateNotFound');
        new State(
            [
                'elements' => [
                    '6' => [
                        'bot' => 'The company’s lawyers...'
                    ]
                ]
            ],
            '3'
        );
    }

    /** @test */
    public function shouldGetBotMessageByState()
    {
        $state = new State(
            [
                'elements' => [
                    '6' => [
                        'bot' => 'The company’s lawyers...'
                    ]
                ]
            ],
            '6'
        );
        
        $result = $state->getBotMessage();
        
        self::assertEquals('The company’s lawyers...', $result);
    }

    /** @test */
    public function shouldGetResponseOptions()
    {
        $state = new State(
            [
                'elements' => [
                    '7' => [
                        'user' => [
                            '8' => [
                                'text' => 'Ok...'
                            ],
                            '9' => [
                                'text' => 'No!'
                            ]
                        ]
                    ]
                ]
            ],
            '7'
        );

        $result = $state->getResponseOptions();

        self::assertEquals(
            [
                ['Ok...'],
                ['No!']
            ],
            $result
        );
    }

    /** @test */
    public function shouldGetFollowup()
    {
        $state = new State(
            [
                'elements' => [
                    '1' => [
                        'bot' => 'Good morning',
                        'followup' => '2'
                    ]
                ]
            ],
            '1'
        );

        $result = $state->getFollowup();

        self::assertEquals('2', $result);
    }

    /** @test */
    public function shouldGetFollowupByMessage()
    {
        $state = new State(
            [
                'elements' => [
                    '7' => [
                        'user' => [
                            '8' => [
                                'text' => 'Ok...',
                                'followup' => '10'
                            ],
                            '9' => [
                                'text' => 'No!',
                                'followup' => '11'
                            ]
                        ]
                    ]
                ]
            ],
            '7'
        );

        $result = $state->getFollowupByMessage('Ok...');

        self::assertEquals('10', $result);
    }

    /** @test */
    public function shouldThrowExceptionWhenUserMessageNotFoundInAvailableOptions()
    {
        $state = new State(
            [
                'elements' => [
                    '7' => [
                        'user' => [
                            '8' => [
                                'text' => 'Ok...',
                                'followup' => '10'
                            ],
                            '9' => [
                                'text' => 'No!',
                                'followup' => '11'
                            ]
                        ]
                    ]
                ]
            ],
            '7'
        );

        $this->setExpectedException('\src\exceptions\UserMessageNotFoundInAvailableOptions');
        $state->getFollowupByMessage('Maybe...');
    }
}
