<?php
/**
 * Stupid Pass - Simple password quality enforcer
 *
 * This class provides a simple way of preventing user from using easy to
 * guess/bruteforce password. It has been develop to get rid of the *crack-lib
 * PHP extension*.
 *
 * It provides simple, yet pretty effective password validation rules. The
 * library introduce 1337 speaking extrapolation. What we mean by this is
 * converting the supplied password to an exhaustive list of possible simple
 * alteration such as changing the letter a by @ or 4. The complete list of
 * alteration can be found below (section 1337 speak conversion table). This list
 * is then compared against common passwords based on researches done on the
 * latest password database breaches (stratfor, sony, phpbb, rockyou, myspace).
 * Additionally, it validates the length and the use of multiple charsets
 * (uppsercase, lowercase, numeric, special). The later reduce drastically the
 * size of the common password list.
 *
 * @author Danny Fullerton - Mantor Organization www.mantor.org
 * @version 1.0
 * @license BSD
 *
 * Usage:
 *   $sp = new StupidPass();
 *   $boolResult = $sp->validate($PasswordToTest);
 */

namespace StupidPass;


class StupidPass
{
    private $lang = array(
        'length' => 'Password must be between %s and %s characters inclusively',
        'upper' => 'Password must contain at least one uppercase character',
        'lower' => 'Password must contain at least one lowercase character',
        'numeric' => 'Password must contain at least one numeric character',
        'special' => 'Password must contain at least one special character',
        'common' => 'Password is too common',
        'environ' => 'Password uses identifiable information and is guessable',
        'onlynumeric' => 'Password must not be entirely numeric'
    );
    private $options = array();
    private $original = null;
    private $pass = array();
    private $errors = array();
    private $minlen = 8; // No, this is not an option.
    private $maxlen = null; // Password max char. Should be set according to your database.
    private $dict = null; // Path to the dictionary
    private $environ = array(); // Regex of environmental info such as the name of the company.

    const DEFAULT_DICTIONARY = 'StupidPass.default.dict';

    /**
     * StupidPass constructor.
     * @param int $maxlen Max password length allowed
     * @param string[] $environ Environment names or strings that might be used as a password to disallow
     * @param null $dict Path to dictionary file
     * @param null $lang Text to return is a specific test fails
     * @param array $options Options for the password validation to disable or enable
     */
    public function __construct($maxlen = 40, $environ = array(), $dict = null, $lang = null, $options = array())
    {
        $this->options = $options;
        if (!array_key_exists("disable", $this->options)) {
            $this->options['disable'] = array();
        }
        if (!array_key_exists("maxlen-guessable-test", $this->options)) {
            $this->options['maxlen-guessable-test'] = 24;
        }

        $this->maxlen = $maxlen;
        $this->environ = $environ;
        $defaultDictionary = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . self::DEFAULT_DICTIONARY);
        $this->dict = (isset($dict)) ? $dict : $defaultDictionary;
        if ($lang != null) {
            $this->lang = $lang;
        }
    }

    /**
     * Validate a password based on the configuration in the constructor.
     * @param string $pass
     * @return bool true if validated, false if failed.  Call $this->getErrors() to retrieve the array of errors.
     * @throws DictionaryNotFoundException
     */
    public function validate($pass)
    {
        $this->errors = null;
        $this->original = $pass;
        if (!in_array('length', $this->options['disable'])) {
            $this->length();
        }
        if (!in_array('upper', $this->options['disable'])) {
            $this->upper();
        }
        if (!in_array('lower', $this->options['disable'])) {
            $this->lower();
        }
        if (!in_array('numeric', $this->options['disable'])) {
            $this->numeric();
        }
        if (!in_array('special', $this->options['disable'])) {
            $this->special();
        }
        if (!in_array('onlynumeric', $this->options['disable'])) {
            $this->onlyNumeric();
        }

        if (strlen($pass) <= $this->options['maxlen-guessable-test']) {
            $this->extrapolate();

            if (!in_array('environ', $this->options['disable'])) {
                $this->environmental();
            }
            if (!in_array('common', $this->options['disable'])) {
                $this->common();
            }
        }

        $this->pass = null;

        return (empty($this->errors));
    }

    /**
     * Retrieve an array of text from lang enumerating the errors.
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    private function length()
    {
        $passLen = strlen($this->original);
        if ($passLen < $this->minlen OR $passLen > $this->maxlen) {
            $err = sprintf($this->lang['length'], $this->minlen, $this->maxlen);
            $this->errors[] = $err;
        }
    }

    private function upper()
    {
        if (!preg_match('/[A-Z]+/', $this->original)) {
            $this->errors[] = $this->lang['upper'];
        }
    }

    private function lower()
    {
        if (!preg_match('/[a-z]+/', $this->original)) {
            $this->errors[] = $this->lang['lower'];
        }
    }

    private function numeric()
    {
        if (!preg_match('/[0-9]+/', $this->original)) {
            $this->errors[] = $this->lang['numeric'];
        }
    }

    private function onlyNumeric()
    {
        if (preg_match('/^[0-9]*$/', $this->original)) {
            $this->errors[] = $this->lang['onlynumeric'];
        }
    }

    private function special()
    {
        if (!preg_match('/[\W_]/', $this->original)) {
            $this->errors[] = $this->lang['special'];
        }
    }

    private function environmental()
    {
        foreach ($this->environ as $env) {
            foreach ($this->pass as $pass) {
                if (preg_match("/$env/i", $pass) == 1) {
                    $this->errors[] = $this->lang['environ'];

                    return;
                }
            }
        }
    }

    /**
     * @throws DictionaryNotFoundException
     */
    private function common()
    {
        $fp = fopen($this->dict, 'r');
        if (!$fp) {
            throw new DictionaryNotFoundException("Can't open file: " . $this->dict);
        }
        while (($buf = fgets($fp, 1024)) !== false) {
            $buf = rtrim($buf);
            foreach ($this->pass as $pass) {
                if ($pass == $buf) {
                    $this->errors[] = $this->lang['common'];

                    return;
                }
            }
        }
    }

    private function extrapolate()
    {
        // don't put too much stuff here, it has exponential performance impact.
        $leet = array(
            '@' => array('a', 'o'),
            '4' => array('a'),
            '8' => array('b'),
            '3' => array('e'),
            '1' => array('i', 'l'),
            '!' => array('i', 'l', '1'),
            '0' => array('o'),
            '$' => array('s', '5'),
            '5' => array('s'),
            '6' => array('b', 'd'),
            '7' => array('t')
        );
        $map = array();
        $pass_array = str_split(strtolower($this->original));
        foreach ($pass_array as $i => $char) {
            $map[$i][] = $char;
            foreach ($leet as $pattern => $replace) {
                if ($char === (string)$pattern) {
                    for ($j = 0, $c = count($replace); $j < $c; $j++) {
                        $map[$i][] = $replace[$j];
                    }
                }
            }
        }
        $this->pass = $this->expand($map);
    }

    // expand all possible password recursively

    private function expand(&$map, $old = array(), $index = 0)
    {
        $new = array();
        foreach ($map[$index] as $char) {
            $c = count($old);
            if ($c == 0) {
                $new[] = $char;
            } else {
                for ($i = 0; $i < $c; $i++) {
                    $new[] = @$old[$i] . $char;
                }
            }
        }
        unset($old);
        $r = ($index == count($map) - 1) ? $new : $this->expand($map, $new, $index + 1);

        return $r;
    }
}
