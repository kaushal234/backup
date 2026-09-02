FROM node:24

WORKDIR /srv/powerbi
COPY powerbi/ /srv/powerbi/

RUN npm install
RUN npm install -g pm2 nodemon

CMD ["tail", "-f", "/dev/null"]