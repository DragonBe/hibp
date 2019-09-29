#!/usr/bin/env bash

if [ -z $HIBP_KEY ]
then
    echo Please set environment variable HIBP_KEY with your API key
    echo Get one at https://haveibeenpwned.com/API/Key and support the project
    exit 1
fi

BASE_URI="https://api.pwnedpasswords.com"
#BASE_URI="https://haveibeenpwned.com/api/v3"

HEADER_UA="User-Agent=DragonBe\Hibp-0.0.2%20Composer\1.6.4%20PHP\7.3"
HEADER_ACCEPT="Accept=application/vnd.haveibeenpwned.v3+json"
HEADER_KEY="hibp-api-key=$HIBP_KEY"

echo Writing test mock 'new1_password.txt'
echo "GET $BASE_URI/range/071CD" > tests/_files/new1_password.txt
echo "" >> tests/_files/new1_password.txt
curl \
    --silent \
    --show-error \
    --request GET \
    --include \
    --header $HEADER_UA \
    --header $HEADER_ACCEPT \
    --header $HEADER_KEY \
    $BASE_URI/range/071CD >> tests/_files/new1_password.txt

echo Writing test mock 'new2_passwords.txt'
echo "GET $BASE_URI/range/6F8FE" > tests/_files/new2_password.txt
echo "" >> tests/_files/new2_password.txt
curl \
    --silent \
    --show-error \
    --request GET \
    --include \
    --header $HEADER_UA \
    --header $HEADER_ACCEPT \
    --header $HEADER_KEY \
    $BASE_URI/range/6F8FE >> tests/_files/new2_password.txt

echo Writing test mock 'new3_passwords.txt'
echo "GET $BASE_URI/range/B23DF" > tests/_files/new3_password.txt
echo "" >> tests/_files/new3_password.txt
curl \
    --silent \
    --show-error \
    --request GET \
    --include \
    --header $HEADER_UA \
    --header $HEADER_ACCEPT \
    --header $HEADER_KEY \
    $BASE_URI/range/B23DF >> tests/_files/new3_password.txt

echo Writing test mock 'pwned1_password.txt'
echo "GET $BASE_URI/range/5BAA6" > tests/_files/pwned1_password.txt
echo "" >> tests/_files/pwned1_password.txt
curl \
    --silent \
    --show-error \
    --request GET \
    --include \
    --header $HEADER_UA \
    --header $HEADER_ACCEPT \
    --header $HEADER_KEY \
    $BASE_URI/range/5BAA6 >> tests/_files/pwned1_password.txt

echo Writing test mock 'pwned2_password.txt'
echo "GET $BASE_URI/range/5EAB7" > tests/_files/pwned2_password.txt
echo "" >> tests/_files/pwned2_password.txt
curl \
    --silent \
    --show-error \
    --request GET \
    --include \
    --header $HEADER_UA \
    --header $HEADER_ACCEPT \
    --header $HEADER_KEY \
    $BASE_URI/range/5EAB7 >> tests/_files/pwned2_password.txt

echo Writing test mock 'pwned3_password.txt'
echo "GET $BASE_URI/range/D033E" > tests/_files/pwned3_password.txt
echo "" >> tests/_files/pwned3_password.txt
curl \
    --silent \
    --show-error \
    --request GET \
    --include \
    --header $HEADER_UA \
    --header $HEADER_ACCEPT \
    --header $HEADER_KEY \
    $BASE_URI/range/D033E >> tests/_files/pwned3_password.txt

echo Writing test mock 'invalid1.txt'
echo "GET $BASE_URI/range" > tests/_files/invalid1.txt
echo "" >> tests/_files/invalid1.txt
curl \
    --silent \
    --show-error \
    --request GET \
    --include \
    --header $HEADER_UA \
    --header $HEADER_ACCEPT \
    --header $HEADER_KEY \
    $BASE_URI/range >> tests/_files/invalid1.txt

echo Writing test mock 'invalid2.txt'
echo "GET $BASE_URI/range/" > tests/_files/invalid2.txt
echo "" >> tests/_files/invalid2.txt
curl \
    --silent \
    --show-error \
    --request GET \
    --include \
    --header $HEADER_UA \
    --header $HEADER_ACCEPT \
    --header $HEADER_KEY \
    $BASE_URI/range/ >> tests/_files/invalid2.txt

echo Writing test mock 'invalid3.txt'
echo "GET $BASE_URI/range/GFEDC" > tests/_files/invalid3.txt
echo "" >> tests/_files/invalid3.txt
curl \
    --silent \
    --show-error \
    --request GET \
    --include \
    --header $HEADER_UA \
    --header $HEADER_ACCEPT \
    --header $HEADER_KEY \
    $BASE_URI/range/GFEDC >> tests/_files/invalid3.txt

