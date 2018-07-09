# Changelog

## v0.0.4 (2018-07-09)

Some updates provided by @freekmurze to make the project better

- #2 (code improvement): Add missing typehint
- #3 (project improvement): Add editorconfig file
- #4 (project improvement): Add .gitattributes
- #5 (project improvement): Consider adding a changelog

## v0.0.3 (2018-06-14)

There was a problem in counting how many hits a given password had (see #1 for details). It was counting all hits for all hashes returned by [HIBP](https://haveibeenpwned.com), not for the password hash itself.

## v0.0.2 (2018-06-12)

This is a small update:

- Updating `README.md` so it explains how you can use this library
- Added simple use case examples in the `examples/` directory

## v0.0.1 (2018-06-12)

This is the first release of `dragonbe/hibp`, a composer package that allows you to verify passwords with [@TroyHunt](https://twitter.com/TroyHunt)'s email and password breach verification website [Have I Been Pwned?](https://haveibeenpwned.com).

What can you do with this package? Verify a clear text or SHA1 hashed password to HIBP to see if the password has been found in a breach. This is a good way to inform your users to choose another password.
