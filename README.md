# Description
StupidPass.class.php is a PHP library to prevent common password attacks. Is it based on researches done on the latest password database breaches and according analysis.

It provides a simple way of preventing user from using easy to guess/bruteforce passwords by implementing the following password requirements:

* at least 8 characters; AND
* at least four charsets (i.e. uppsercase, lowercase, numeric and special charactere); AND
* the supply password is extrapolate in 1337 speak encoding (e.g. admin=@dm1n : 1=i,4=a,0=o, etc); AND 
    * most not match common weak passwords obtained by analysing the latests compromised password databases (stratfor, sony, phpbb, myspace); AND
    * most not be derived by the environmental context (e.g. the name of the company, the name of the application, the name of the site, the username, etc).
    
# 1337 speak conversion
* @ => a OR o
* 4 => a
* 8 => b
* 3 => e
* 1 => i OR l
* ! => i OR l OR 1
* 0 => o
* $ => s OR 5
* 5 => s
* 7 => t

# Usage
Simplest usage would look something like this:

    $sp = new StupidPass();
    $bool = $sp->validate($PasswordToTest);

The most complex usage could look like this:

    $hardlang = array(
    'length' => 'must be between %s and %s characters inclusively',
    'upper'  => 'must contain at least one uppercase character',
    'lower'  => 'must contain at least one lowercase character',
    'numeric'=> 'must contain at least one numeric character',
    'special'=> 'must contain at least one special character',
    'common' => 'is way too common! Come on, help yourself!',
    'environ'=> "WTF?!? Don't use the name of our website as your password!");
    
    $sp = new StupidPass(40, array('github'), 'StupidPass.default.dict', $hardlang);
    if($sp->validate($PasswordToTest) === false) {
      $err = $sp-get_errors();
      print("Your password is weak:<br \>");
      foreach($err as $e) {
        print($e."<br />");
      }
    }

# Todo

* Post some test.

# License
BSD license. In other word it's free software, free as in free beer.

# Authors
Danny Fullerton - Mantor Organization
