#!/usr/bin/env bash

gulp full-build --prod

mkdir -p ./dist
rm -rf ./dist/*

cp -r ./fcon ./dist/
cp ./license.txt ./dist/fcon/
cd ./dist && zip -q -r fcon.zip fcon

rm -rf ./fcon
