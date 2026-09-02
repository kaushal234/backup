FROM alpine:latest AS base

RUN apk add --no-cache  \
    ansible \
    openssh-client

WORKDIR /provisioning

ADD provisioning/roles/requirements.yml /provisioning/roles/
ADD provisioning/collections/requirements.yml /provisioning/collections/
RUN ansible-galaxy role install -r /provisioning/roles/requirements.yml
RUN ansible-galaxy collection install -r /provisioning/collections/requirements.yml --collections-path /usr/share/ansible/collections

FROM base AS dev
# only dev tools

FROM base AS prod
