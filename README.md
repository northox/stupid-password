# Overview
StupidPass.class.php provides a simple way of preventing user from using easy to guess/bruteforce password. It has been develop to get rid of the *crack-lib PHP extension*.

# Description
StupidPass.class.php is a PHP library that provides simple, yet pretty effective password validation rules.

The library implements 1337 speaking extrapolation. This converts the supplied password into an exhaustive list of possible simple alterations, such as changing the letter `a` to `@` or `4`, which is bordly used by end user to meet complexity rules. The complete list of alterations can be found below. This list is then compared against common passwords based on researcs done on the latest password database breaches (linkedin, stratfor, sony, phpbb, rockyou, myspace). Additionally, it validates the length and use of multiple charsets (uppsercase, lowercase, numeric, special) - the later drastically reducing the size of the common password list.

Here's the requirements:

* ensure the length is at least 8 characters; AND
* ensure is contains 4 charsets (i.e. uppercase, lowercase, numeric and special characters); AND
* if environmental context is supplied, the list must not match the environmental context (regex) (e.g. the name of the company, the name of the application, the name of the site, the username, etc); AND
* the list must not match with the supplied dictionary which is based on common weak passwords obtained by analysing the latest compromised password databases (stratfor, sony, phpbb, myspace, etc).

Additionally:

* Online attacks should be mitigated by implementing anti-bruteforce techniques; 
  * e.g. [nicht anti-bruteforce](https://github.com/northox/nicht/blob/master/lib/nicht/Nicht.class.php#L633)
* Offline attacks should be mitigated by using strong hashing algorithm such as PBKDF2.
  * e.g. [nicht](https://github.com/northox/nicht/blob/master/src/admin.php#L58) [PBKDF2](https://github.com/northox/nicht/blob/master/lib/nicht/MysqliNichtAuthPbkdf2.class.php#L65)

## Some maths
The minimum possible combination provided by stupid password is: lowercase + uppercase + numeric + special = (26 + 26 + 10 + 10)^8 = 72^8 = 7.222041363×10¹⁴

n.b. I consider only 10 possibilities for special characters as most users only use what's on top of the numbers (from a keyboard perspective).

If you consider loosing up the requirements, be advised that removing the numeric OR special charset (62^8 = 2.183401056×10¹⁴) is better than using 7 character passwords (72^7 = 1.0030613×10¹³) with all charsets.

## 1337 speak conversion table

    @ => a, o  
    4 => a
    8 => b
    3 => e
    1 => i, l
    ! => i, l, 1
    0 => o
    $ => s, 5
    5 => s
    6 => b, d
    7 => t

## Usage
Simplest possible usage looks like this:

```php
use StupidPass;
$simplePass = new StupidPass();
$bool = $simplePass->validate($PasswordToTest);
```

The most complex usage scenario could look like this:

```php
// Override the default errors messages
$hardlang = array(
  'length'      => 'Password must be between %s and %s characters inclusively',
  'upper'       => 'Password must contain at least one uppercase character',
  'lower'       => 'Password must contain at least one lowercase character',
  'numeric'     => 'Password must contain at least one numeric character',
  'special'     => 'Password must contain at least one special character',
  'common'      => 'Password is too common',
  'environ'     => 'Password uses identifiable information and is guessable',
  'onlyNumeric' => 'Password must not be entirely numeric'
);

// Supply reference of the environment (company, hostname, username, etc)
$environmental = array('northox', 'github', 'stupidpass', 'stupidpassword');

// Additional options
$options = array(
  'disable' => array('special'),
);

// The first parameter is the max length
use StupidPass;
$stupidPass = new StupidPass(40, $environmental, './StupidPass.default.dict', $hardlang, $options);
if($stupidPass->validate($PasswordToTest) === false) {
  print('Your password is weak:<br \>');
  foreach($stupidPass->getErrors() as $error) {
    print($error . '<br />');
  }
}
```

Possible options:
* 'disable' (array): disable stated tests, e.g. `array('special', 'lower')` to disable both the test for special and lowercase characters.
* 'maxlen-guessable-test' (integer): disable environment and common password checks for passwords longer than given integer (due to high memory usage and cpu usage). Default: 24.

Please be advised that the minimum length requirement of 8 is hard-coded and can not be changed.

## PHP Unit Tests
```bash
$ ./vendor/bin/phpunit tests/Tests/StupidPassTest.php
PHPUnit 3.7.38 by Sebastian Bergmann.

.......................

Time: 43 ms, Memory: 4.00MB

OK (23 tests, 45 assertions)
```

# License
BSD license. In other words, it's free software, almost free as in free beer.

# Source
https://github.com/northox/stupid-password

# Authors
Danny Fullerton - Mantor Organization

# Contributors
- Stephan Bösch-Plepelits aka [plepe](https://github.com/plepe)
- Nathan Feger aka [nafeger](https://github.com/nafeger)
- Mike McLin aka [mikemclin](https://github.com/mikemclin)
- René Roth aka [reneroth](https://github.com/reneroth)
- John Koniges aka [venar](https://github.com/venar)
