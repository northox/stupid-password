<?php
namespace StupidPass\Tests;

use StupidPass\DictionaryNotFoundException;
use StupidPass\StupidPass;

class StupidPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider NoEnvironmentPasswordCheck
     *
     * @param string $password
     * @param bool $expectedResult
     * @param int $numberOfErrors
     * @throws \StupidPass\DictionaryNotFoundException
     */
    public function testValidate($password, $expectedResult, $numberOfErrors)
    {
        $stupidPassword = new StupidPass();
        $this->assertEquals($expectedResult, $stupidPassword->validate($password));
        $errors = $stupidPassword->getErrors();
        $this->assertEquals($numberOfErrors, count($errors), "Errors expected $numberOfErrors, found: " . count($errors));
    }

    /**
     * @dataProvider WithEnvironmentPasswordCheck
     *
     * @param string $password
     * @param bool $expectedResult
     * @param int $numberOfErrors
     * @throws \StupidPass\DictionaryNotFoundException
     */
    public function testValidateEnvironment($password, $expectedResult, $numberOfErrors)
    {
        $environmentVariables = ['google'];

        $stupidPassword = new StupidPass(40, $environmentVariables);
        $this->assertEquals($expectedResult, $stupidPassword->validate($password));
        $errors = $stupidPassword->getErrors();
        $this->assertEquals($numberOfErrors, count($errors), "Errors expected $numberOfErrors, found: " . count($errors));
    }

    public function testCheckDictionaryNotFoundException()
    {
        $this->setExpectedException(DictionaryNotFoundException::class);

        $stupidPass = new StupidPass(40, [], 'fileDoesNotExist.dict');
        $stupidPass->validate('password');
    }

    public function NoEnvironmentPasswordCheck()
    {
        return $this->passwordsToTest(false);
    }

    public function WithEnvironmentPasswordCheck()
    {
        return $this->passwordsToTest(true);
    }

    private function passwordsToTest($failEnvironmentPass = false)
    {
        $passwords = [
            ['football', false, 4],
            ['fOOtb4ll', false, 2],
            ['pr1nce55', false, 3],
            ['b4byg1r1', false, 3],
            ['passw0rd', false, 3],
            ['P@55W0r6', false, 1],
            ['zxcasdqwe', false, 3],
            ['zxc45dqw3', false, 2],
            ['aPf1#@_GHe', true, 0],
            ['437818ec5af53a3ba6e5cb9435fe177fALKO', false, 1],
        ];

        $environmentPass = ['@Go0gle!', true, 0];
        if ($failEnvironmentPass) {
            $environmentPass[1] = false;
            $environmentPass[2] = 1;
        }

        $passwords[] = $environmentPass;

        return $passwords;
    }
}
