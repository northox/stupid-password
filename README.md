# Overview
StupidPass.class.php provides a simple way of preventing user from using easy to guess/bruteforce password. It has been develop to get rid of the *crack-lib PHP extension*.

# Description
StupidPass.class.php is a PHP library that provides simple, yet pretty effective password validation rules. The library implements 1337 speaking extrapolation. What we mean by this is converting the supplied password into an exhaustive list of possible simple alteration such as changing the letter a by @ or 4 which is bordly used by end user to meet complexity rules. The complete list of alteration can be found below (section 1337 speak conversion table). This list is then compared against common passwords based on researches done on the latest password database breaches (linkedin, stratfor, sony, phpbb, rockyou, myspace). Additionally, it validates the length and the use of multiple charsets (uppsercase, lowercase, numeric, special). The later reduce drastically the size of the common password list.

Here's the requirements:

* ensure the length is greater or equal to 8 characters; AND
* ensure is contains 4 charsets (i.e. uppsercase, lowercase, numeric and special characters); AND
* if environmental context is supplied, the list must not match the environmental context (regex) (e.g. the name of the company, the name of the application, the name of the site, the username, etc). AND
* the list must not match with the supplied dictionary which is based on common weak passwords obtained by analysing the latest compromised password databases (stratfor, sony, phpbb, myspace, etc);

Additionally:

* Online attacks should be mitigated by implementing anti-bruteforce techniques (e.g. [nicht anti-bruteforce](https://github.com/northox/nicht/blob/master/lib/nicht/Nicht.class.php#L633)).
* Offline attacks should be mitigated by using strong hashing algorithm such as PBKDF2 (e.g. [nicht](https://github.com/northox/nicht/blob/master/src/admin.php#L58) [PDKDF2](https://github.com/northox/nicht/blob/master/lib/nicht/MysqliNichtAuthPbkdf2.class.php#L65)).

## Some maths
The minimum possible combination provided by stupid password is: lowercase + uppercase + numeric + special = (26 + 26 + 10 + 10)^8 = 72^8 = 7.222041363×10¹⁴

n.b. I consider only 10 possiblities for special characters as most users only use what's on top of the numbers (from a keyboard perspective).

If you consider loosing up the requirements, be advise that it is better to remove the numeric OR special charset (62^8 = 2.183401056×10¹⁴) then to use 7 characters passwords (72^7 = 1.0030613×10¹³) with all the charsets.

## 1337 speak conversion table

    @ => a OR o  
    4 => a
    8 => b
    3 => e
    1 => i OR l
    ! => i OR l OR 1
    0 => o
    $ => s OR 5
    5 => s
    6 => b OR d
    7 => t

## Usage
Simplest usage would look something like this:

```php
$sp = new StupidPass();
$bool = $sp->validate($PasswordToTest);
```

The most complex usage scenario could look like this:

```php
// Override the default errors messages
$hardlang = array(
'length' => 'must be between %s and %s characters inclusively',
'upper'  => 'must contain at least one uppercase character',
'lower'  => 'must contain at least one lowercase character',
'numeric'=> 'must contain at least one numeric character',
'special'=> 'must contain at least one special character',
'common' => 'is way too common! Come on, help yourself!',
'environ'=> "WTF?!? Don't use the name of our website as your password!");

// Supply reference of the environment (company, hostname, username, etc)
$environmental = array('northox', 'github', 'stupidpass', 'stupidpassword');

$sp = new StupidPass(40, $environmental, './StupidPass.default.dict', $hardlang);
if($sp->validate($PasswordToTest) === false) {
  print("Your password is weak:<br \>");
  foreach($sp->get_errors() as $e) {
    print($e."<br />");
  }
}
```

## Test
Here's some test:

    $ php test.php 
    FAIL:  football
    FAIL:  fOOtb4ll
    FAIL:  pr1nce55
    FAIL:  b4byg1r1
    FAIL:  passw0rd
    FAIL:  P@55W0r6
    FAIL:  zxcasdqwe
    FAIL:  zxc45dqw3
    PASS:  aPf1#@_GHe

# License
BSD license. In other word it's free software, free as in free beer.

# Source
https://github.com/northox/stupid-password

# Authors
Danny Fullerton - Mantor Organization
