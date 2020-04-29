FROM node:13-alpine

RUN apk add curl && curl --compressed -o- -L https://yarnpkg.com/install.sh | sh

RUN yarn policies set-version '^1.22.4'

RUN yarn global add nodemon
