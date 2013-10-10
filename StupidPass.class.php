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
*   $boolResult = $sp->valitate($PasswordToTest);
*/
 
class StupidPass
{
  private $lang = array(
    'length' => 'Password must be between %s and %s characters inclusively',
    'upper'  => 'Password must contain at least one uppercase character',
    'lower'  => 'Password must contain at least one lowercase character',
    'numeric'=> 'Password must contain at least one numeric character',
    'special'=> 'Password must contain at least one special character',
    'common' => 'Password is too common',
    'environ'=> 'Password use identifiable information and is guessable'
  );
  private $original = null;
  private $pass = array();
  private $errors = array();
  private $minlen = 8;    // No, this is not an option.
  private $maxlen = null; // Password max char. Should be set according to your database.
  private $dict = null;   // Path to the dictionary
  private $environ = array(); // Regex of environmental info such as the name of the company.

  public function __construct($maxlen = 40, $environ = array(), $dict = null, $lang = null)
  {
    $this->maxlen = $maxlen;
    $this->environ = $environ;
    $this->dict = (isset($dict)) ? $dict: 'StupidPass.default.dict';
    if ($lang != null) $this->lang = $lang;
  }
 
  public function validate($pass)
  {
    $this->errors = null;
    $this->original = $pass;
    $this->length();
    $this->upper();
    $this->lower();
    $this->numeric();
    $this->special();
   
    $this->extrapolate();
    
    $this->environmental();
    $this->common();
    $this->pass = null;
    return(empty($this->errors));
  }
 
  public function get_errors()
  {
    return $this->errors;
  }
 
  private function length()
  {
    $passlen = strlen($this->original);
    if($passlen < $this->minlen OR $passlen > $this->maxlen) {
      $err = sprintf($this->lang['length'], $this->minlen, $this->maxlen);
      $this->errors[] = $err;
    }
  }
 
  private function upper()
  {
    if(!preg_match('/[A-Z]+/', $this->original)) {
      $this->errors[] = $this->lang['upper'];
    }
  }
 
  private function lower()
  {
    if(!preg_match('/[a-z]+/', $this->original)) {
      $this->errors[] = $this->lang['lower'];
    }
  }
 
  private function numeric()
  {
    if(!preg_match('/[0-9]+/', $this->original)) {
      $this->errors[] = $this->lang['numeric'];
    }
  }
 
  private function special()
  {
    if(!preg_match('/[\W_]/', $this->original)) {
      $this->errors[] = $this->lang['special'];
    }
  }
  
  private function environmental()
  {
    foreach($this->environ as $env) {
      if (strlen($env) > 3) {
        foreach($this->pass as $pass) {
          if(stristr($pass, $env) !== false) {
            $this->errors[] = $this->lang['environ'];
            return;
          }
        }
      }
    }
  }
  
  private function common()
  {
    $fp = fopen($this->dict, 'r');
    if(!$fp) throw new Exception("Can't open file: ".$this->dict);
    while(($buf = fgets($fp, 1024)) !== false) {
      $buf = rtrim($buf);
      foreach($this->pass as $pass) {
        if($pass == $buf) {
          $this->errors[] = $this->lang['common'];
          return;
        }
      }
    }
  }

  private function extrapolate()
  {
    // don't put too much stuff here, it has exponential performance impact.
    $leet = array('@'=>array('a', 'o'),
                  '4'=>array('a'),
                  '8'=>array('b'),
                  '3'=>array('e'),
                  '1'=>array('i', 'l'),
                  '!'=>array('i','l','1'),
                  '0'=>array('o'),
                  '$'=>array('s','5'),
                  '5'=>array('s'),
                  '6'=>array('b', 'd'),
                  '7'=>array('t')
                 );
    $map = array();
    $pass_array = str_split(strtolower($this->original));
    foreach($pass_array as $i => $char) {
      $map[$i][] = $char;
      foreach($leet as $pattern => $replace) {
        if($char === (string)$pattern) {
          for($j=0,$c=count($replace); $j<$c; $j++) {
            $map[$i][] = $replace[$j];
          }
        }
      }
    }
    $this->pass = $this->expand($map);
  }
 
  // expand all possible password recursively
  private function expand(&$map, $old = array(), $index = 0) {
    $new = array();
    foreach($map[$index] as $char) {
      $c = count($old);
      if($c == 0) {
        $new[] = $char;
      } else {
        for($i=0,$c=count($old); $i<$c; $i++) {
          $new[] = @$old[$i].$char;
        }
      }
    }
    unset($old);
    $r = ($index == count($map)-1) ? $new : $this->expand($map, $new, $index + 1);
    return $r;
  }
}
?>
