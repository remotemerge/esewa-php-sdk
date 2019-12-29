GIT_FILE=./init.sh
ENV_FILE=./.env
PHPUNIT_FILE=phpunit.xml

if [ ! -f "$GIT_FILE" ]; then
    cp ./.init.default.sh ./.init.sh
fi

if [ ! -f "$ENV_FILE" ]; then
    cp ./.env.default ./.env
fi

php -S localhost:8090
