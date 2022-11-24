#!/bin/bash

cat <<< $(jq --indent 4 --arg version $VERSION '.require |= with_entries(if (.key|test("^symfony/")) then .value=$version else . end)' < composer.json) > composer.json
cat <<< $(jq --indent 4 --arg version $VERSION '."require-dev" |= with_entries(if (.key|test("^symfony/")) then .value=$version else . end)' < composer.json) > composer.json
