# Have I been pwned Composer package

To increse security of users on your system, I started building a client for [@TroyHunt](https://twitter.com/troyhunt)'s [Have I Been Pwned?](https://haveibeenpwned.com/) API v2 that will check if a given password was already used in a breach. Many thanks to Mr. Troy Hunt for providing us this service.

## Project scope

The goal of this project is to have a [composer package](https://packagist.org) that will allow you to quickly verify if a given password (from a registration or password reset form) was found in a data breach so you can inform your users to change their password and thus improving overal security.

This project was also the subject of my talk [Mutation Testing with Infection](https://www.meetup.com/PHP-Leuven-Web-Innovation-Group/events/sctxfnyxjbkb/) where the code base was not only covered by unit tests, but also was subjected to **Mutation Testing** using [Infection](https://infection.github.io/) to ensure no coding mistakes could slip into the codebase.

## Getting started

First of all you need to add this library to your project. The easiest way is to use [Composer](https://getcomposer.org).

```
composer require dragonbe/hibp
```

If you want to quickly test the functionality, copy/paste the following code in a file named `hibp.php`.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$hibp = \Dragonbe\Hibp\HibpFactory::create();
echo 'Password "password": ' . ($hibp->isPwnedPassword('password') ? 'Pwned' : 'OK') . PHP_EOL;
echo 'Password "NVt3MpvQ": ' . ($hibp->isPwnedPassword('NVt3MpvQ') ? 'Pwned' : 'OK') . PHP_EOL;

```

Now run this file to make sure all is working fine.

```
php hibp.php
```

If all works well, you should see the following result:

```
Password "password": Pwned
Password "NVt3MpvQ": OK
```

### Getting number of hits found in HIBP

Sometimes you want to display a number of hits found for a given password. Just call `count()` on your `$hibp` instance or call `$hibp->count()` directly.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$hibp = \Dragonbe\Hibp\HibpFactory::create();
$passwords = ['password', 'NVt3MpvQ'];
foreach ($passwords as $password) {
    $found = $hibp->isPwnedPassword($password);
    $count = count($hibp);

    echo sprintf(
        'Password "%s": %s',
        $password,
        $found ? ('Pwned (' . $count . ' times)') : 'OK'
    ) . PHP_EOL;
}

```

This will give you a more detailed view on how many times a password has been used that was found in breaches collected in [Have I Been Pwned?](https://haveibeenpwned.com).

```
Password "password": Pwned (3311463 times)
Password "NVt3MpvQ": OK
```

For more details please check out the unit test directory `tests/` to understand what exceptions can occur and what other options there are to use this library.

## Roadmap

Even though this is the beginning of the project, I want to make full use of HIBP API by searching on usernames and email addresses to see if they were discovered in breaches. This might be convenient to alert users that they might want to use a more secure password or change all their passwords for the provided credential.

In short, these are the goals I want to accomplish in the near future:

- check for existence of credential (username/email address) in HIBP Sites
- check for existence of credential (username/email address) in HIBP Pastes

And who knows, maybe when people are using this library more ideas will be provided

## Acknowledgement

This library wasn't possible if [Mr. Troy Hunt](https://twitter.com/TroyHunt) didn't spend his valuable time in feeding breached data in his database and providing his site [haveibeenpwned.com](https://haveibeenpwned.com). So thank you good sir for putting such great efforts in HIBP.

## License

I've provided this project "as-is" and I licensed it with an [MIT license](LICENSE) so you can use it freely in your projects.

## Questions, suggestions, feedback of issues

Please use [this project's issue feature](https://github.com/DragonBe/hibp/issues) to reach out to me with your suggestions. I love your feedback and also interested in the use cases where you have used this library in.
