# Overview
StupidPass.class.php provides a simple way of preventing user from using easy to guess/bruteforce password. It has been develop to get rid of the *crack-lib PHP extension*.

# Description
StupidPass.class.php is a PHP library that provides simple, yet pretty effective password validation rules. The library introduce 1337 speaking extrapolation. What we mean by this is converting the supplied password to an exhaustive list of possible simple alteration such as changing the letter a by @ or 4. The complete list of alteration can be found below (section 1337 speak conversion table). This list is then compared against common passwords based on researches done on the latest password database breaches (stratfor, sony, phpbb, rockyou, myspace). Additionally, it validates the length and the use of multiple charsets (uppsercase, lowercase, numeric, special). The later reduce drastically the size of the common password list.

Here's the requirements:

* ensure the length is greater or equal to 8 characters; AND
* ensure is contains 4 charsets (i.e. uppsercase, lowercase, numeric and special characters);
    * if environmental context is supplied, the list must not match the environmental context (regex) (e.g. the name of the company, the name of the application, the name of the site, the username, etc).
    * the list must not match with the supplied dictionary which is based on common weak passwords obtained by analysing the latest compromised password databases (stratfor, sony, phpbb, myspace, etc); AND
    
# 1337 speak conversion table

    @ => a OR o  
    4 => a
    8 => b
    3 => e
    1 => i OR l
    ! => i OR l OR 1
    0 => o
    $ => s OR 5
    5 => s
    6 => b
    7 => t

# Usage
Simplest usage would look something like this:

    $sp = new StupidPass();
    $bool = $sp->validate($PasswordToTest);

The most complex usage could look like this:

    // Override the default errors messages
    $hardlang = array(
    'length' => 'must be between %s and %s characters inclusively',
    'upper'  => 'must contain at least one uppercase character',
    'lower'  => 'must contain at least one lowercase character',
    'numeric'=> 'must contain at least one numeric character',
    'special'=> 'must contain at least one special character',
    'common' => 'is way too common! Come on, help yourself!',
    'environ'=> "WTF?!? Don't use the name of our website as your password!");
    
    // Supply reference ot the environment (company, hostname, username, etc)
    $environmental = array('northox', 'github', 'stupidpass', 'stupidpassword');
    
    $sp = new StupidPass(40, $environmental, './StupidPass.default.dict', $hardlang);
    if($sp->validate($PasswordToTest) === false) {
      $err = $sp->get_errors();
      print("Your password is weak:<br \>");
      foreach($err as $e) {
        print($e."<br />");
      }
    }

# Test

Let's take the must common passwords 

    $ php test.php 
    FAIL:  football
    FAIL:  fOOtb4ll
    FAIL:  pr1nce55
    FAIL:  b4byg1r1
    FAIL:  passw0rd
    FAIL:  P@55w0r6
    FAIL:  zxcasdqwe
    FAIL:  zxc45dqw3

# License
BSD license. In other word it's free software, free as in free beer.

# Authors
Danny Fullerton - Mantor Organization
